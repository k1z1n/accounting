<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestDirectCurlCommand extends Command
{
    protected $signature = 'test:direct-curl {site}';
    protected $description = 'Тестирование прямого curl запроса';

    public function handle()
    {
        $site = $this->argument('site');

        if (!in_array($site, ['obama', 'ural'])) {
            $this->error('Доступные сайты: obama, ural');
            return 1;
        }

        $urls = [
            'obama' => 'https://obama.ru/prmmxchngr',
            'ural' => 'https://ural-obmen.ru/prmmxchngr'
        ];

        $url = $urls[$site];
        $this->info("🧪 Тестируем {$site}: {$url}");

        // Используем curl напрямую
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
                'Accept-Encoding: gzip, deflate',
                'Connection: keep-alive',
                'Cache-Control: no-cache',
                'Pragma: no-cache',
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $this->error("❌ Ошибка curl: {$error}");
            return 1;
        }

        if ($httpCode !== 200) {
            $this->error("❌ HTTP код: {$httpCode}");
            return 1;
        }

        $this->info("✅ HTTP код: {$httpCode}");
        $this->info("📄 Размер ответа: " . strlen($response) . " байт");

        // Анализируем содержимое
        if (strpos($response, 'Авторизация') !== false) {
            $this->info("✅ Страница авторизации найдена");
        } else {
            $this->warn("⚠️ Страница авторизации не найдена");
        }

        // Ищем форму
        if (strpos($response, 'ajax_post_form') !== false) {
            $this->info("✅ Форма логина найдена");
        } else {
            $this->warn("⚠️ Форма логина не найдена");
        }

        // Ищем CAPTCHA
        if (strpos($response, 'captcha1') !== false && strpos($response, 'captcha2') !== false) {
            $this->info("✅ CAPTCHA найдена");
        } else {
            $this->warn("⚠️ CAPTCHA не найдена");
        }

        // Ищем salt
        if (strpos($response, 'name="salt"') !== false) {
            $this->info("✅ Salt найден");
        } else {
            $this->warn("⚠️ Salt не найден");
        }

        // Ищем action URL
        if (preg_match('/action="([^"]*premium_admin_action[^"]*)"/', $response, $matches)) {
            $this->info("✅ Action URL: {$matches[1]}");
        } else {
            $this->warn("⚠️ Action URL не найден");
        }

        $this->info("🎯 Тестирование завершено");
        return 0;
    }
}




