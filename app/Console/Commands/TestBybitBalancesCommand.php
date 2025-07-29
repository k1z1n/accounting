<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestBybitBalancesCommand extends Command
{
    protected $signature = 'test:bybit-balances';
    protected $description = 'Тестирование получения балансов Bybit с подробным выводом';

    public function handle()
    {
        $this->info('Тестирование получения балансов Bybit...');

        $cfg = config("services.bybit.main");
        if (!$cfg) {
            $this->error("Конфигурация Bybit не найдена");
            return 1;
        }

        $apiKey = $cfg['api_key'] ?? null;
        $secretKey = $cfg['secret_key'] ?? null;

        if (!$apiKey || !$secretKey) {
            $this->error("Bybit API ключи не настроены");
            return 1;
        }

        $testnet = $cfg['testnet'] ?? false;
        $baseUrl = $testnet ? 'https://api-testnet.bybit.com' : 'https://api.bybit.com';

        $this->info("Используем URL: {$baseUrl}");
        $this->info("API Key: " . substr($apiKey, 0, 10) . "...");
        $this->info("Testnet: " . ($testnet ? 'Да' : 'Нет'));

        // Тест 1: Получение баланса кошелька
        $this->info("\n=== ТЕСТ 1: Получение баланса кошелька ===");
        $this->testWalletBalance($baseUrl, $apiKey, $secretKey);

        // Тест 2: Получение баланса Funding Account
        $this->info("\n=== ТЕСТ 2: Получение баланса Funding Account ===");
        $this->testFundingBalance($baseUrl, $apiKey, $secretKey);

        // Тест 3: Получение доступных для вывода средств
        $this->info("\n=== ТЕСТ 3: Получение доступных для вывода средств ===");
        $this->testWithdrawalAmount($baseUrl, $apiKey, $secretKey);

        // Тест 4: Получение баланса с параметром coin
        $this->info("\n=== ТЕСТ 4: Получение баланса с параметром coin ===");
        $this->testWalletBalanceWithCoin($baseUrl, $apiKey, $secretKey);

        // Тест 5: Получение баланса Spot Account
        $this->info("\n=== ТЕСТ 5: Получение баланса Spot Account ===");
        $this->testSpotBalance($baseUrl, $apiKey, $secretKey);

        // Тест 6: Получение баланса без параметра accountType
        $this->info("\n=== ТЕСТ 6: Получение баланса без параметра accountType ===");
        $this->testWalletBalanceWithoutAccountType($baseUrl, $apiKey, $secretKey);

        // Тест 7: Получение баланса через Asset API
        $this->info("\n=== ТЕСТ 7: Получение баланса через Asset API ===");
        $this->testAssetBalance($baseUrl, $apiKey, $secretKey);

        return 0;
    }

    private function testWalletBalance(string $baseUrl, string $apiKey, string $secretKey): void
    {
        $timestamp = time() * 1000;
        $endpoint = '/v5/account/wallet-balance';
        $params = [
            'accountType' => 'UNIFIED'
        ];

        $queryString = http_build_query($params);
        $signature = $this->generateSignature($secretKey, $timestamp, $apiKey, 'GET', $endpoint, $queryString);

        $this->info("Endpoint: {$endpoint}");
        $this->info("Params: " . json_encode($params));
        $this->info("Query string: {$queryString}");

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
    }

    private function testFundingBalance(string $baseUrl, string $apiKey, string $secretKey): void
    {
        $timestamp = time() * 1000;
        $endpoint = '/v5/account/wallet-balance';
        $params = [
            'accountType' => 'FUND'
        ];

        $queryString = http_build_query($params);
        $signature = $this->generateSignature($secretKey, $timestamp, $apiKey, 'GET', $endpoint, $queryString);

        $this->info("Endpoint: {$endpoint}");
        $this->info("Params: " . json_encode($params));
        $this->info("Query string: {$queryString}");

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
    }

    private function testWithdrawalAmount(string $baseUrl, string $apiKey, string $secretKey): void
    {
        $timestamp = time() * 1000;
        $endpoint = '/v5/account/withdrawal';
        $params = [
            'coinName' => 'USDT,BTC,ETH,USDC'
        ];

        $queryString = http_build_query($params);
        $signature = $this->generateSignature($secretKey, $timestamp, $apiKey, 'GET', $endpoint, $queryString);

        $this->info("Endpoint: {$endpoint}");
        $this->info("Params: " . json_encode($params));
        $this->info("Query string: {$queryString}");

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
    }

    private function testWalletBalanceWithCoin(string $baseUrl, string $apiKey, string $secretKey): void
    {
        $timestamp = time() * 1000;
        $endpoint = '/v5/account/wallet-balance';
        $params = [
            'accountType' => 'UNIFIED',
            'coin' => 'USDT'
        ];

        $queryString = http_build_query($params);
        $signature = $this->generateSignature($secretKey, $timestamp, $apiKey, 'GET', $endpoint, $queryString);

        $this->info("Endpoint: {$endpoint}");
        $this->info("Params: " . json_encode($params));
        $this->info("Query string: {$queryString}");

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
    }

    private function testSpotBalance(string $baseUrl, string $apiKey, string $secretKey): void
    {
        $timestamp = time() * 1000;
        $endpoint = '/v5/account/wallet-balance';
        $params = [
            'accountType' => 'SPOT'
        ];

        $queryString = http_build_query($params);
        $signature = $this->generateSignature($secretKey, $timestamp, $apiKey, 'GET', $endpoint, $queryString);

        $this->info("Endpoint: {$endpoint}");
        $this->info("Params: " . json_encode($params));
        $this->info("Query string: {$queryString}");

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
    }

    private function testWalletBalanceWithoutAccountType(string $baseUrl, string $apiKey, string $secretKey): void
    {
        $timestamp = time() * 1000;
        $endpoint = '/v5/account/wallet-balance';
        $params = []; // No params for this test

        $queryString = http_build_query($params);
        $signature = $this->generateSignature($secretKey, $timestamp, $apiKey, 'GET', $endpoint, $queryString);

        $this->info("Endpoint: {$endpoint}");
        $this->info("Params: " . json_encode($params));
        $this->info("Query string: {$queryString}");

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
    }

    private function testAssetBalance(string $baseUrl, string $apiKey, string $secretKey): void
    {
        $timestamp = time() * 1000;
        $endpoint = '/v5/asset/wallet-balance';
        $params = [
            'accountType' => 'UNIFIED'
        ];

        $queryString = http_build_query($params);
        $signature = $this->generateSignature($secretKey, $timestamp, $apiKey, 'GET', $endpoint, $queryString);

        $this->info("Endpoint: {$endpoint}");
        $this->info("Params: " . json_encode($params));
        $this->info("Query string: {$queryString}");

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
    }

    private function generateSignature(string $secretKey, int $timestamp, string $apiKey, string $method, string $endpoint, string $queryString = ''): string
    {
        $paramStr = $timestamp . $apiKey . '5000' . $queryString;
        return hash_hmac('sha256', $paramStr, $secretKey);
    }
}
