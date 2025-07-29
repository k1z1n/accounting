<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestBybitFundingCommand extends Command
{
    protected $signature = 'test:bybit-funding';
    protected $description = 'Тестирование получения баланса Funding Account Bybit';

    public function handle()
    {
        $this->info('Тестирование получения баланса Funding Account Bybit...');

        $cfg = config("services.bybit.funding");
        if (!$cfg) {
            $this->error("Конфигурация Bybit Funding не найдена");
            $this->info("Добавьте в .env файл:");
            $this->info("BYBIT_FUNDING_API_KEY=your_funding_api_key");
            $this->info("BYBIT_FUNDING_SECRET_KEY=your_funding_secret_key");
            return 1;
        }

        $apiKey = $cfg['api_key'] ?? null;
        $secretKey = $cfg['secret_key'] ?? null;

        if (!$apiKey || !$secretKey) {
            $this->error("Bybit Funding API ключи не настроены");
            $this->info("Добавьте в .env файл:");
            $this->info("BYBIT_FUNDING_API_KEY=your_funding_api_key");
            $this->info("BYBIT_FUNDING_SECRET_KEY=your_funding_secret_key");
            return 1;
        }

        $testnet = $cfg['testnet'] ?? false;
        $baseUrl = $testnet ? 'https://api-testnet.bybit.com' : 'https://api.bybit.com';

        $this->info("Используем URL: {$baseUrl}");
        $this->info("API Key: " . substr($apiKey, 0, 10) . "...");
        $this->info("Testnet: " . ($testnet ? 'Да' : 'Нет'));

        $timestamp = time() * 1000;
        $endpoint = '/v5/account/wallet-balance';
        $params = [
            'accountType' => 'UNIFIED'
        ];

        $queryString = http_build_query($params);
        $signature = $this->generateSignature($secretKey, $timestamp, $apiKey, 'GET', $endpoint, $queryString);

        $this->info("Endpoint: {$endpoint}");
        $this->info("Params: " . json_encode($params));

        $response = Http::timeout(10)
            ->withHeaders([
                'X-BAPI-API-KEY' => $apiKey,
                'X-BAPI-SIGN' => $signature,
                'X-BAPI-SIGN-TYPE' => '2',
                'X-BAPI-TIMESTAMP' => $timestamp,
                'X-BAPI-RECV-WINDOW' => '5000',
            ])
            ->get($baseUrl . $endpoint . '?' . $queryString);

        $this->info("Status: " . $response->status());
        $this->info("Response: " . $response->body());

        if ($response->successful()) {
            $data = $response->json();
            $this->info("Parsed response:");
            $this->line(json_encode($data, JSON_PRETTY_PRINT));
        }

        return 0;
    }

    private function generateSignature(string $secretKey, int $timestamp, string $apiKey, string $method, string $endpoint, string $queryString = ''): string
    {
        $paramStr = $timestamp . $apiKey . '5000' . $queryString;
        return hash_hmac('sha256', $paramStr, $secretKey);
    }
}
