<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendExchangerBalancesCommand extends Command
{
    protected $signature = 'telegram:send-balances {--provider=} {--exchanger=}';
    protected $description = 'Отправить балансы обменников в Telegram';

    private TelegramService $telegramService;
    private array $providers = ['heleket' => 'Heleket', 'rapira' => 'Rapira', 'bybit' => 'Bybit'];
    private array $exchangers = ['obama' => 'Obama', 'ural' => 'Ural', 'main' => 'Main'];

    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    public function handle()
    {
        $this->info('Начинаем сбор балансов обменников...');

        $provider = $this->option('provider');
        $exchanger = $this->option('exchanger');

        $balancesData = [];

        // Определяем какие провайдеры и обменники обрабатывать
        $providersToProcess = empty($provider) || $provider === 'all' ? array_keys($this->providers) : [$provider];
        $exchangersToProcess = empty($exchanger) || $exchanger === 'all' ? array_keys($this->exchangers) : [$exchanger];

        foreach ($providersToProcess as $provider) {
            $this->info("Получаем балансы для {$this->providers[$provider]}...");

            if ($provider === 'bybit') {
                // Bybit имеет только один обменник - "main"
                $exchangersForProvider = ['main'];
            } elseif ($provider === 'heleket' || $provider === 'rapira') {
                // Heleket и Rapira имеют только "obama" и "ural"
                $exchangersForProvider = ['obama', 'ural'];
            } else {
                $exchangersForProvider = $exchangersToProcess;
            }

            foreach ($exchangersForProvider as $exchanger) {
                $this->info("  - {$this->exchangers[$exchanger]}...");

                $balances = $this->getBalances($provider, $exchanger);
                if ($balances !== null) {
                    $balancesData[$this->providers[$provider]][$this->exchangers[$exchanger]] = $balances;
                    $this->info("  ✓ Балансы получены");
                } else {
                    $this->warn("  ⚠ Не удалось получить балансы");
                }
            }
        }

        if (empty($balancesData)) {
            $this->error('Не удалось получить данные балансов');
            return 1;
        }

        $this->info('Отправляем данные в Telegram...');

        if ($this->telegramService->sendExchangerBalances($balancesData)) {
            $this->info('✓ Данные успешно отправлены в Telegram');
            return 0;
        } else {
            $this->error('✗ Ошибка отправки в Telegram');
            return 1;
        }
    }

    /**
     * Получить балансы для конкретного провайдера и обменника
     */
    private function getBalances(string $provider, string $exchanger): ?array
    {
        $cfg = config("services.{$provider}.{$exchanger}");
        if (!$cfg) {
            Log::error("SendExchangerBalancesCommand: не найдена конфигурация", [
                'provider' => $provider,
                'exchanger' => $exchanger
            ]);
            return null;
        }

        try {
            if ($provider === 'heleket') {
                $url = $cfg['balance_url'];
                return $this->getHeleketBalances($cfg, $url);
            } elseif ($provider === 'rapira') {
                $url = $cfg['balance_url'];
                return $this->getRapiraBalances($cfg, $url);
            } elseif ($provider === 'bybit') {
                return $this->getBybitBalances($cfg);
            } else {
                Log::error("SendExchangerBalancesCommand: неизвестный провайдер", ['provider' => $provider]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error("SendExchangerBalancesCommand: ошибка получения балансов", [
                'provider' => $provider,
                'exchanger' => $exchanger,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Получить балансы Heleket
     */
    private function getHeleketBalances(array $cfg, string $url): array
    {
        Log::info("SendExchangerBalancesCommand: запрос Heleket балансов", ['url' => $url]);

        $body = json_encode([]);
        $sign = md5(base64_encode($body) . $cfg['api_key']);

        $response = Http::withHeaders([
            'merchant' => $cfg['merchant_uuid'],
            'sign' => $sign,
            'Content-Type' => 'application/json',
        ])->timeout(10)->post($url, []);

        if (!$response->successful()) {
            throw new \Exception("HTTP {$response->status()}: " . $response->body());
        }

        $raw = $response->json();
        Log::info("SendExchangerBalancesCommand: Heleket сырые данные", ['raw' => $raw]);

        $normalized = $this->normalizeHeleket($raw);
        Log::info("SendExchangerBalancesCommand: Heleket нормализованные данные", ['normalized' => $normalized]);

        return $normalized;
    }

    /**
     * Получить балансы Rapira
     */
    private function getRapiraBalances(array $cfg, string $url): array
    {
        Log::info("SendExchangerBalancesCommand: запрос Rapira балансов", ['url' => $url]);

        $privateKey = $this->preparePrivateKey($cfg['private_key']);

        $jwt = $this->makeJwt([
            'exp' => time() + 1800,
            'jti' => bin2hex(random_bytes(12)),
        ], $privateKey);

        $tokenResponse = Http::timeout(10)->post('https://api.rapira.net/open/generate_jwt', [
            'kid' => $cfg['uid'],
            'jwt_token' => $jwt,
        ]);

        if (!$tokenResponse->successful()) {
            throw new \Exception("Ошибка получения токена: " . $tokenResponse->body());
        }

        $tokenData = $tokenResponse->json();
        $rapiraToken = $tokenData['token'] ?? null;

        if (!$rapiraToken) {
            throw new \Exception("Токен не найден в ответе");
        }

        $balanceResponse = Http::timeout(10)->withHeaders([
            'Authorization' => 'Bearer ' . $rapiraToken,
        ])->get($url);

        if (!$balanceResponse->successful()) {
            throw new \Exception("Ошибка получения баланса: " . $balanceResponse->body());
        }

        $raw = $balanceResponse->json();
        Log::info("SendExchangerBalancesCommand: Rapira сырые данные", ['raw' => $raw]);

        $normalized = $this->normalizeRapira($raw);
        Log::info("SendExchangerBalancesCommand: Rapira нормализованные данные", ['normalized' => $normalized]);

        return $normalized;
    }

    /**
     * Получить балансы Bybit
     */
    private function getBybitBalances(array $cfg): array
    {
        Log::info("SendExchangerBalancesCommand: запрос Bybit балансов");

        $apiKey = $cfg['api_key'] ?? null;
        $secretKey = $cfg['secret_key'] ?? null;

        if (!$apiKey || !$secretKey) {
            Log::warning("SendExchangerBalancesCommand: Bybit API ключи не настроены");
            return [];
        }

        $testnet = $cfg['testnet'] ?? false;
        $baseUrl = $testnet ? 'https://api-testnet.bybit.com' : 'https://api.bybit.com';

        $timestamp = round(microtime(true) * 1000);
        $recvWindow = 5000;

        $endpoint = '/v5/asset/transfer/query-account-coins-balance';
        $params = [
            'accountType' => 'FUND'
        ];

        $queryString = http_build_query($params);
        $url = $baseUrl . $endpoint . '?' . $queryString;

        // Правильная строка для подписи для Bybit Trading API v5
        $signaturePayload = $timestamp . $apiKey . $recvWindow . $queryString;
        $signature = hash_hmac('sha256', $signaturePayload, $secretKey);

        Log::info("SendExchangerBalancesCommand: Bybit запрос", [
            'url' => $url,
            'timestamp' => $timestamp,
            'queryString' => $queryString,
            'signaturePayload' => $signaturePayload
        ]);

        $response = Http::timeout(10)
            ->withHeaders([
                'X-BAPI-API-KEY' => $apiKey,
                'X-BAPI-SIGN' => $signature,
                'X-BAPI-TIMESTAMP' => $timestamp,
                'X-BAPI-RECV-WINDOW' => $recvWindow,
                'Content-Type' => 'application/json',
            ])
            ->get($url);

        if (!$response->successful()) {
            throw new \Exception("HTTP {$response->status()}: " . $response->body());
        }

        $raw = $response->json();
        Log::info("SendExchangerBalancesCommand: Bybit сырые данные", ['raw' => $raw]);

        $normalized = $this->normalizeBybit($raw);
        Log::info("SendExchangerBalancesCommand: Bybit нормализованные данные", ['normalized' => $normalized]);

        return $normalized;
    }

    private function generateBybitSignature(string $secretKey, int $timestamp, string $apiKey, string $method, string $endpoint, string $queryString = ''): string
    {
        $paramStr = $timestamp . $apiKey . '5000' . $queryString;
        return hash_hmac('sha256', $paramStr, $secretKey);
    }

    /**
     * Подготовить приватный ключ для Rapira
     */
    private function preparePrivateKey(string $privateKey): string
    {
        if (str_contains($privateKey, '\\n')) {
            $privateKey = str_replace('\\n', "\n", $privateKey);
        }

        $privateKey = trim($privateKey);

        if (base64_decode($privateKey, true) !== false && !str_contains($privateKey, '-----BEGIN')) {
            $privateKey = base64_decode($privateKey);
        }

        if (!str_starts_with($privateKey, '-----BEGIN')) {
            $body = preg_replace('/\s+/', '', $privateKey);
            $body = trim(chunk_split($body, 64, "\n"));
            $privateKey = "-----BEGIN PRIVATE KEY-----\n" . $body . "\n-----END PRIVATE KEY-----";
        }

        return $privateKey;
    }

    /**
     * Создать JWT токен
     */
    private function makeJwt(array $payload, string $privateKey): string
    {
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $headerEncoded = $this->urlsafeB64(json_encode($header));
        $payloadEncoded = $this->urlsafeB64(json_encode($payload));

        $data = $headerEncoded . '.' . $payloadEncoded;
        $signature = '';

        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $signatureEncoded = $this->urlsafeB64($signature);

        return $data . '.' . $signatureEncoded;
    }

    /**
     * URL-safe base64 encoding
     */
    private function urlsafeB64(string $input): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($input));
    }

    /**
     * Нормализовать данные Heleket
     */
    private function normalizeHeleket(array $raw): array
    {
        $result = [];

        // Проверяем структуру ответа
        if (!isset($raw['result']) || !is_array($raw['result']) || empty($raw['result'])) {
            Log::warning("SendExchangerBalancesCommand: неожиданная структура ответа Heleket", ['raw' => $raw]);
            return $result;
        }

        // Берем первый элемент из result
        $balanceData = $raw['result'][0]['balance'] ?? null;
        if (!$balanceData) {
            Log::warning("SendExchangerBalancesCommand: не найдены данные баланса в ответе Heleket");
            return $result;
        }

        if (isset($balanceData['merchant']) && is_array($balanceData['merchant'])) {
            $result['merchant'] = array_map(function($item) {
                return [
                    'code' => $item['currency_code'] ?? '',
                    'name' => $item['currency_code'] ?? '', // Используем code как name
                    'amount' => (float)($item['balance'] ?? 0),
                    'icon' => '', // Иконки не предоставляются API
                ];
            }, $balanceData['merchant']);
        }

        if (isset($balanceData['user']) && is_array($balanceData['user'])) {
            $result['user'] = array_map(function($item) {
                return [
                    'code' => $item['currency_code'] ?? '',
                    'name' => $item['currency_code'] ?? '', // Используем code как name
                    'amount' => (float)($item['balance'] ?? 0),
                    'icon' => '', // Иконки не предоставляются API
                ];
            }, $balanceData['user']);
        }

        Log::info("SendExchangerBalancesCommand: Heleket нормализованные данные", [
            'merchant_count' => count($result['merchant'] ?? []),
            'user_count' => count($result['user'] ?? [])
        ]);

        return $result;
    }

    /**
     * Нормализовать данные Rapira
     */
    private function normalizeRapira(array $raw): array
    {
        Log::info("SendExchangerBalancesCommand: нормализация Rapira", ['raw_structure' => array_keys($raw)]);

        // Rapira возвращает массив напрямую, без обертки в data
        if (is_array($raw) && !isset($raw['data'])) {
            $list = $raw;
        } else {
            $list = $raw['data'] ?? [];
        }

        if (empty($list)) {
            Log::warning("SendExchangerBalancesCommand: пустой список балансов Rapira");
            return [];
        }

        $normalized = array_map(function($item) {
            return [
                'code' => $item['unit'] ?? '',
                'name' => $item['name'] ?? ($item['unit'] ?? ''),
                'amount' => (float)($item['balance'] ?? 0),
                'icon' => '', // Иконки не предоставляются API
            ];
        }, $list);

        Log::info("SendExchangerBalancesCommand: Rapira нормализованные данные", [
            'count' => count($normalized),
            'sample' => array_slice($normalized, 0, 3)
        ]);

        return $normalized;
    }

    /**
     * Нормализовать данные Bybit
     */
    private function normalizeBybit(array $raw): array
    {
        Log::info("SendExchangerBalancesCommand: нормализация Bybit", ['raw_structure' => array_keys($raw)]);

        $normalized = [];

        if (isset($raw['result']['balance']) && is_array($raw['result']['balance'])) {
            foreach ($raw['result']['balance'] as $coin) {
                if (isset($coin['coin']) && isset($coin['walletBalance'])) {
                    $amount = (float)$coin['walletBalance'];
                    if ($amount > 0) {
                        $normalized[] = [
                            'code' => strtoupper($coin['coin']),
                            'name' => strtoupper($coin['coin']),
                            'amount' => $amount,
                            'icon' => '', // Иконки не предоставляются API
                        ];
                    }
                }
            }
        }

        Log::info("SendExchangerBalancesCommand: Bybit нормализованные данные", [
            'count' => count($normalized),
            'sample' => array_slice($normalized, 0, 3)
        ]);

        return $normalized;
    }
}
