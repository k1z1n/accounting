<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CookieManagerService
{
    private array $exchangers = [
        'obama' => [
            'login_url' => 'https://obama.ru/prmmxchngr',
            'login_action_url' => 'https://obama.ru/premium_admin_action-pn_admin_login.html',
            'admin_url' => 'https://obama.ru/wp-admin/admin.php?page=pn_bids&page_num=1',
            'username' => null,
            'password' => null,
            'pin' => null,
        ],
        'ural' => [
            'login_url' => 'https://ural-obmen.ru/prmmxchngr',
            'login_action_url' => 'https://ural-obmen.ru/premium_admin_action-pn_admin_login.html',
            'admin_url' => 'https://ural-obmen.ru/wp-admin/admin.php?page=pn_bids&page_num=1',
            'username' => null,
            'password' => null,
            'pin' => null,
        ]
    ];

    public function __construct()
    {
        // Получаем учетные данные из конфигурации
        $this->exchangers['obama']['username'] = config('exchanger.obama.username');
        $this->exchangers['obama']['password'] = config('exchanger.obama.password');
        $this->exchangers['obama']['pin'] = config('exchanger.obama.pin');
        $this->exchangers['ural']['username'] = config('exchanger.ural.username');
        $this->exchangers['ural']['password'] = config('exchanger.ural.password');
        $this->exchangers['ural']['pin'] = config('exchanger.ural.pin');
    }

    /**
     * Получить актуальные куки для обменника
     */
    public function getFreshCookies(string $exchangerName): ?string
    {
        if (!isset($this->exchangers[$exchangerName])) {
            Log::error("Неизвестный обменник: {$exchangerName}");
            return null;
        }

        $config = $this->exchangers[$exchangerName];

        // Проверяем кэш
        $cacheKey = "cookies_{$exchangerName}";
        $cachedCookies = Cache::get($cacheKey);

        if ($cachedCookies && $this->validateCookies($exchangerName, $cachedCookies)) {
            Log::info("Используем кэшированные куки для {$exchangerName}");
            return $cachedCookies;
        }

        // Пытаемся получить новые куки
        $newCookies = $this->loginAndGetCookies($exchangerName, $config);

        if ($newCookies) {
            // Кэшируем на 30 минут (1800 секунд) для большей надежности
            Cache::put($cacheKey, $newCookies, 1800);
            Log::info("Получены новые куки для {$exchangerName}");
            return $newCookies;
        }

        Log::error("Не удалось получить куки для {$exchangerName}");
        return null;
    }

    /**
     * Войти и получить куки
     */
    private function loginAndGetCookies(string $exchangerName, array $config): ?string
    {
        try {
            Log::info("=== Начинаем авторизацию для {$exchangerName} ===");

            // --- Шаг 1: Получаем страницу логина через curl ---
            $loginUrl = $config['login_url'];
            Log::info("Шаг 1: Получаем страницу логина: {$loginUrl}");

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $loginUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                CURLOPT_HTTPHEADER => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
                    'Accept-Encoding: gzip, deflate',
                    'Connection: keep-alive',
                    'Cache-Control: no-cache',
                    'Pragma: no-cache',
                ],
                CURLOPT_HEADER => true,
                CURLOPT_ENCODING => '',
            ]);
            $response = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headers = substr($response, 0, $header_size);
            $html = substr($response, $header_size);
            $cookies = '';
            if (preg_match_all('/Set-Cookie: ([^;]+);/i', $headers, $matches)) {
                $cookies = implode('; ', $matches[1]);
            }
            curl_close($ch);

            Log::info("Шаг 1: Страница получена, размер HTML: " . strlen($html) . " байт");

            // --- Шаг 2: Извлекаем action URL и все input-поля ---
            Log::info("Шаг 2: Извлекаем action URL и все input-поля из формы");
            $formData = $this->extractFormFields($html);
            if (!$formData) {
                Log::error("Не удалось извлечь action URL и поля формы");
                Log::error("HTML (first 15000 chars):\n" . mb_substr($html, 0, 15000));
                return null;
            }
            $actionUrl = $formData['action_url'];
            $fields = $formData['fields'];

            // --- Задержка для обхода антибота ---
            Log::info("Ждем 2 секунды перед отправкой формы (антибот)");
            sleep(2);

            // --- Шаг 3: Извлекаем исходную CAPTCHA ---
            Log::info("Шаг 3: Извлекаем исходную CAPTCHA");
            $captchaData = $this->extractCaptchaData($html);

            // --- Шаг 4: Обновляем CAPTCHA через AJAX (как в браузере) ---
            Log::info("Шаг 4: Обновляем CAPTCHA через AJAX");
            $captchaReloadUrl = $this->extractCaptchaReloadUrl($html);
            if ($captchaReloadUrl) {
                $newCaptchaData = $this->reloadCaptcha($captchaReloadUrl, $cookies, $config);
                if ($newCaptchaData) {
                    Log::info("CAPTCHA обновлена через AJAX");
                    $captchaData = $newCaptchaData;
                } else {
                    Log::warning("Не удалось обновить CAPTCHA через AJAX, используем исходную");
                }
            } else {
                Log::warning("URL для обновления CAPTCHA не найден, используем исходную");
            }

            // --- Шаг 5: Решаем CAPTCHA ---
            $captchaAnswer = $this->solveCaptcha($captchaData);
            if ($captchaAnswer === null) {
                Log::error("Не удалось решить CAPTCHA для {$exchangerName}");
                return null;
            }
            Log::info("Шаг 3: CAPTCHA решена, ответ: {$captchaAnswer}");

            // --- Шаг 4: Формируем данные для POST ---
            // Подставляем реальные значения
            $fields['logmail'] = (string)$config['username'];
            $fields['pass'] = (string)$config['password'];
            // Оставляем user_pin как есть из формы (пустая строка), если PIN не указан в конфигурации
            if (!empty($config['pin'])) {
                $fields['user_pin'] = (string)$config['pin'];
            }
            // Если PIN не указан в конфигурации, оставляем значение из формы (обычно пустая строка)
            Log::info("Тип данных для number: " . gettype($captchaAnswer));
            $fields['number'] = (string)$captchaAnswer; // строка
            // submit оставляем как есть ("Войти")

            Log::info("Данные для отправки: " . json_encode($fields));
            Log::info("Куки для POST: " . $cookies);

            $loginData = http_build_query($fields);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $actionUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                CURLOPT_HTTPHEADER => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
                    'Accept-Encoding: gzip, deflate',
                    'Connection: keep-alive',
                    'Cache-Control: no-cache',
                    'Pragma: no-cache',
                    'Referer: ' . $loginUrl,
                    'Content-Type: application/x-www-form-urlencoded',
                    'Cookie: ' . $cookies,
                    'X-Requested-With: XMLHttpRequest',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                ],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $loginData,
                CURLOPT_HEADER => true,
                CURLOPT_ENCODING => '',
            ]);
            $response = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headers = substr($response, 0, $header_size);
            $body = substr($response, $header_size);
            $cookiesArr = [];
            if (preg_match_all('/Set-Cookie: ([^;]+);/i', $headers, $matches)) {
                $cookiesArr = $matches[1];
            }
            curl_close($ch);

            Log::info("Шаг 4: POST-запрос выполнен, размер ответа: " . strlen($body) . " байт");
            Log::error("Login POST response (first 2000 chars):\n" . mb_substr($body, 0, 2000));

            if (empty($cookiesArr)) {
                Log::error("Не удалось извлечь куки из ответа для {$exchangerName}");
                return null;
            }

            $finalCookies = implode('; ', $cookiesArr);
            Log::info("Шаг 4: Куки извлечены успешно: " . substr($finalCookies, 0, 50) . "...");
            Log::info("=== Авторизация для {$exchangerName} завершена успешно ===");

            return $finalCookies;
        } catch (\Exception $e) {
            Log::error("Исключение при получении куки для {$exchangerName}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Извлечь salt из HTML
     */
    private function extractSalt(string $html): ?string
    {
        if (preg_match('/name="salt" value="([^"]+)"/', $html, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Извлечь action URL и все input-поля из формы логина
     */
    private function extractFormFields(string $html): ?array
    {
        // Найти форму
        if (!preg_match('/<form[^>]*method="post"[^>]*action="([^"]*)"[^>]*>(.*?)<\/form>/s', $html, $formMatches)) {
            Log::error("Форма не найдена в HTML");
            return null;
        }

        $actionUrl = $formMatches[1];
        $formContent = $formMatches[2];

        // Если action URL относительный, делаем его абсолютным
        if (strpos($actionUrl, 'http') !== 0) {
            $baseUrl = parse_url($this->exchangers['obama']['login_url'], PHP_URL_SCHEME) . '://' . parse_url($this->exchangers['obama']['login_url'], PHP_URL_HOST);
            $actionUrl = $baseUrl . $actionUrl;
        }

        Log::info("Action URL: " . $actionUrl);

        // Извлекаем все input-поля
        $fields = [];
        if (preg_match_all('/<input[^>]*name="([^"]*)"[^>]*value="([^"]*)"[^>]*>/i', $formContent, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $name = $match[1];
                $value = $match[2];
                $fields[$name] = $value;
                Log::info("Найдено поле: {$name} = {$value}");
            }
        }

        // Также ищем поля без value
        if (preg_match_all('/<input[^>]*name="([^"]*)"[^>]*>/i', $formContent, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $name = $match[1];
                if (!isset($fields[$name])) {
                    $fields[$name] = '';
                    Log::info("Найдено поле без значения: {$name}");
                }
            }
        }

        if (empty($fields)) {
            Log::error("Не найдено ни одного input-поля в форме");
            return null;
        }

        return [
            'action_url' => $actionUrl,
            'fields' => $fields
        ];
    }

    /**
     * Извлечь данные CAPTCHA из HTML
     */
    private function extractCaptchaData(string $html): ?array
    {
        // Ищем изображения CAPTCHA
        if (!preg_match('/<img[^>]*class="captcha1"[^>]*src="([^"]*)"/i', $html, $captcha1Match)) {
            Log::error("Не найдено изображение captcha1");
            return null;
        }

        if (!preg_match('/<img[^>]*class="captcha2"[^>]*src="([^"]*)"/i', $html, $captcha2Match)) {
            Log::error("Не найдено изображение captcha2");
            return null;
        }

        // Ищем операцию
        if (!preg_match('/<span[^>]*class="captcha_sym"[^>]*>([^<]*)<\/span>/i', $html, $operationMatch)) {
            Log::error("Не найдена операция CAPTCHA");
            return null;
        }

        $operation = trim($operationMatch[1]);
        $captcha1Url = $captcha1Match[1];
        $captcha2Url = $captcha2Match[1];

        // Делаем URL абсолютными
        if (strpos($captcha1Url, 'http') !== 0) {
            $baseUrl = parse_url($this->exchangers['obama']['login_url'], PHP_URL_SCHEME) . '://' . parse_url($this->exchangers['obama']['login_url'], PHP_URL_HOST);
            $captcha1Url = $baseUrl . $captcha1Url;
        }

        if (strpos($captcha2Url, 'http') !== 0) {
            $baseUrl = parse_url($this->exchangers['obama']['login_url'], PHP_URL_SCHEME) . '://' . parse_url($this->exchangers['obama']['login_url'], PHP_URL_HOST);
            $captcha2Url = $baseUrl . $captcha2Url;
        }

        Log::info("CAPTCHA данные: операция = {$operation}, URL1 = {$captcha1Url}, URL2 = {$captcha2Url}");

        return [
            'operation' => $operation,
            'captcha1_url' => $captcha1Url,
            'captcha2_url' => $captcha2Url
        ];
    }

    /**
     * Решить CAPTCHA
     */
    private function solveCaptcha(array $captchaData): ?int
    {
        try {
            // Используем улучшенный решатель CAPTCHA
            $captchaSolver = new AdvancedCaptchaSolver();
            return $captchaSolver->solveArithmeticCaptcha($captchaData);
        } catch (\Exception $e) {
            Log::error("Ошибка при решении CAPTCHA: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Извлечь URL для обновления CAPTCHA
     */
    private function extractCaptchaReloadUrl(string $html): ?string
    {
        if (preg_match('/url:\s*"([^"]*captchaap_reload[^"]*)"/i', $html, $matches)) {
            $url = $matches[1];

            // Делаем URL абсолютным
            if (strpos($url, 'http') !== 0) {
                $baseUrl = parse_url($this->exchangers['obama']['login_url'], PHP_URL_SCHEME) . '://' . parse_url($this->exchangers['obama']['login_url'], PHP_URL_HOST);
                $url = $baseUrl . $url;
            }

            Log::info("URL для обновления CAPTCHA: " . $url);
            return $url;
        }

        Log::warning("URL для обновления CAPTCHA не найден");
        return null;
    }

    /**
     * Обновить CAPTCHA через AJAX
     */
    private function reloadCaptcha(string $url, string $cookies, array $config): ?array
    {
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json, text/javascript, */*; q=0.01',
                    'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
                    'X-Requested-With: XMLHttpRequest',
                    'Referer: ' . $config['login_url'],
                    'Cookie: ' . $cookies,
                ],
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            if ($response === false) {
                Log::error("Ошибка при обновлении CAPTCHA");
                return null;
            }

            $data = json_decode($response, true);
            if (!$data) {
                Log::error("Не удалось декодировать JSON ответ CAPTCHA");
                return null;
            }

            // Делаем URL абсолютными
            $baseUrl = parse_url($config['login_url'], PHP_URL_SCHEME) . '://' . parse_url($config['login_url'], PHP_URL_HOST);

            return [
                'operation' => $data['nsym'] ?? '+',
                'captcha1_url' => $baseUrl . $data['ncapt1'],
                'captcha2_url' => $baseUrl . $data['ncapt2']
            ];

        } catch (\Exception $e) {
            Log::error("Исключение при обновлении CAPTCHA: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Проверить валидность куки
     */
    private function validateCookies(string $exchangerName, string $cookies): bool
    {
        try {
            $config = $this->exchangers[$exchangerName];
            $adminUrl = $config['admin_url'];

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $adminUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                CURLOPT_HTTPHEADER => [
                    'Cookie: ' . $cookies,
                ],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Проверяем, что страница загружается и не содержит признаков страницы логина
            if ($httpCode === 200 && $response && strpos($response, 'logmail') === false && strpos($response, 'captcha') === false) {
                Log::info("Куки для {$exchangerName} валидны");
                return true;
            }

            Log::warning("Куки для {$exchangerName} невалидны (HTTP: {$httpCode})");
            return false;

        } catch (\Exception $e) {
            Log::error("Ошибка при валидации куки для {$exchangerName}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Извлечь куки из ответа
     */
    private function extractCookies($response): string
    {
        $cookies = '';
        if (preg_match_all('/Set-Cookie: ([^;]+);/i', $response, $matches)) {
            $cookies = implode('; ', $matches[1]);
        }
        return $cookies;
    }

    /**
     * Обновить куки для всех обменников
     */
    public function refreshAllCookies(): array
    {
        $results = [];

        foreach (array_keys($this->exchangers) as $exchangerName) {
            $results[$exchangerName] = [
                'success' => false,
                'cookies' => null,
                'error' => null
            ];

            try {
                $cookies = $this->getFreshCookies($exchangerName);
                if ($cookies) {
                    $results[$exchangerName]['success'] = true;
                    $results[$exchangerName]['cookies'] = $cookies;
                } else {
                    $results[$exchangerName]['error'] = 'Не удалось получить куки';
                }
            } catch (\Exception $e) {
                $results[$exchangerName]['error'] = $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Получить статус куки для всех обменников
     */
    public function getCookiesStatus(): array
    {
        $status = [];

        foreach ($this->exchangers as $name => $config) {
            $status[$name] = [
                'has_credentials' => !empty($config['username']) && !empty($config['password']),
                'has_pin' => !empty($config['pin']),
                'current_cookies_valid' => false,
                'can_auto_refresh' => config("exchanger.{$name}.auto_refresh_cookies", true)
            ];

            // Проверяем текущие куки
            $currentCookies = config("exchanger.{$name}.cookie");
            if ($currentCookies) {
                $status[$name]['current_cookies_valid'] = $this->validateCookies($name, $currentCookies);
            }
        }

        return $status;
    }
}





