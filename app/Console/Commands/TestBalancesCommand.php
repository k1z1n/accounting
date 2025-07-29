<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestBalancesCommand extends Command
{
    protected $signature = 'test:balances {provider} {exchanger}';
    protected $description = 'Тестирование получения балансов';

    public function handle()
    {
        $provider = $this->argument('provider');
        $exchanger = $this->argument('exchanger');

        $this->info("Тестирование получения балансов для {$provider}/{$exchanger}");

        $cfg = config("services.{$provider}.{$exchanger}");
        if (!$cfg) {
            $this->error("Конфигурация не найдена для {$provider}.{$exchanger}");
            return 1;
        }

        $this->info("Конфигурация найдена:");
        $this->line("URL: " . ($cfg['balance_url'] ?? 'не указан'));
        $this->line("Другие параметры: " . implode(', ', array_keys($cfg)));

        try {
            if ($provider === 'heleket') {
                $this->testHeleket($cfg);
            } elseif ($provider === 'rapira') {
                $this->testRapira($cfg);
            } else {
                $this->error("Неизвестный провайдер: {$provider}");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("Ошибка: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function testHeleket(array $cfg)
    {
        $this->info("Тестирование Heleket...");

        $body = json_encode([]);
        $sign = md5(base64_encode($body) . $cfg['api_key']);

        $this->line("Подготовленные данные:");
        $this->line("Body: {$body}");
        $this->line("Sign: {$sign}");
        $this->line("Merchant: " . ($cfg['merchant_uuid'] ?? 'не указан'));

        $response = Http::withHeaders([
            'merchant' => $cfg['merchant_uuid'],
            'sign' => $sign,
            'Content-Type' => 'application/json',
        ])->timeout(10)->post($cfg['balance_url'], []);

        $this->line("HTTP Status: " . $response->status());
        $this->line("Response Body: " . $response->body());

        if ($response->successful()) {
            $data = $response->json();
            $this->info("Успешный ответ:");
            $this->line(json_encode($data, JSON_PRETTY_PRINT));
        }
    }

    private function testRapira(array $cfg)
    {
        $this->info("Тестирование Rapira...");

        $this->line("UID: " . ($cfg['uid'] ?? 'не указан'));
        $this->line("Private Key length: " . strlen($cfg['private_key'] ?? ''));

        // Здесь можно добавить тестирование JWT генерации
        $this->warn("Тестирование Rapira требует дополнительной настройки JWT");
    }
}
