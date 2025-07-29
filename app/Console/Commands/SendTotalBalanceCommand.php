<?php

namespace App\Console\Commands;

use App\Services\HeleketService;
use App\Services\RapiraService;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class SendTotalBalanceCommand extends Command
{
    protected $signature = 'telegram:send-total';
    protected $description = 'Отправить только общий итог балансов в Telegram';

    public function handle()
    {
        $this->info('Собираем данные для общего итога...');

        $heleketService = app(HeleketService::class);
        $rapiraService = app(RapiraService::class);
        $telegramService = app(TelegramService::class);

        $allBalances = [];
        $grandTotal = 0.0;

        // Получаем балансы Heleket
        try {
            $this->info('Получаем балансы Heleket...');
            $heleketBalances = $this->getHeleketBalances();
            if (!empty($heleketBalances)) {
                $allBalances['Heleket'] = $heleketBalances;
                $heleketTotal = $this->calculateProviderTotal($heleketBalances);
                $grandTotal += $heleketTotal;
                $this->info("✓ Heleket: {$heleketTotal}$");
            }
        } catch (\Exception $e) {
            $this->error("Ошибка получения балансов Heleket: " . $e->getMessage());
        }

        // Получаем балансы Rapira
        try {
            $this->info('Получаем балансы Rapira...');
            $rapiraBalances = $this->getRapiraBalances();
            if (!empty($rapiraBalances)) {
                $allBalances['Rapira'] = $rapiraBalances;
                $rapiraTotal = $this->calculateProviderTotal($rapiraBalances);
                $grandTotal += $rapiraTotal;
                $this->info("✓ Rapira: {$rapiraTotal}$");
            }
        } catch (\Exception $e) {
            $this->error("Ошибка получения балансов Rapira: " . $e->getMessage());
        }

        // Получаем балансы Bybit
        try {
            $this->info('Получаем балансы Bybit...');
            $bybitBalances = $this->getBybitBalances();
            if (!empty($bybitBalances)) {
                $allBalances['Bybit'] = $bybitBalances;
                $bybitTotal = $this->calculateProviderTotal($bybitBalances);
                $grandTotal += $bybitTotal;
                $this->info("✓ Bybit: {$bybitTotal}$");
            }
        } catch (\Exception $e) {
            $this->error("Ошибка получения балансов Bybit: " . $e->getMessage());
        }

        if (empty($allBalances)) {
            $this->error('Не удалось получить данные балансов');
            return 1;
        }

        $this->info("Общий итог: {$grandTotal}$");
        $this->info('Отправляем общий итог в Telegram...');

        // Формируем сообщение только с общим итогом
        $message = $this->formatTotalMessage($allBalances, $grandTotal);

        if ($telegramService->sendMessage($message)) {
            $this->info('✓ Общий итог успешно отправлен в Telegram');
            return 0;
        } else {
            $this->error('Ошибка отправки в Telegram');
            return 1;
        }
    }

    private function calculateProviderTotal(array $providerBalances): float
    {
        $total = 0.0;
        $bybitService = app(\App\Services\BybitService::class);

        foreach ($providerBalances as $exchanger => $data) {
            if (isset($data['merchant']) && is_array($data['merchant'])) {
                $total += $bybitService->calculateTotalUsd($data['merchant']);
            }
            if (isset($data['user']) && is_array($data['user'])) {
                $total += $bybitService->calculateTotalUsd($data['user']);
            }
            if (!isset($data['merchant']) && !isset($data['user']) && is_array($data)) {
                $total += $bybitService->calculateTotalUsd($data);
            }
        }

        return $total;
    }

    private function formatTotalMessage(array $allBalances, float $grandTotal): string
    {
        $date = now()->format('d.m.Y');
        $message = "💵 <b>ОБЩИЙ БАЛАНС НА {$date}</b>\n\n";

        $providerTotals = [];

        foreach ($allBalances as $provider => $exchangers) {
            $providerTotal = 0.0;

            foreach ($exchangers as $exchanger => $data) {
                $exchangerTotal = 0.0;

                if (isset($data['merchant']) && is_array($data['merchant'])) {
                    $exchangerTotal += app(\App\Services\BybitService::class)->calculateTotalUsd($data['merchant']);
                }
                if (isset($data['user']) && is_array($data['user'])) {
                    $exchangerTotal += app(\App\Services\BybitService::class)->calculateTotalUsd($data['user']);
                }
                if (!isset($data['merchant']) && !isset($data['user']) && is_array($data)) {
                    $exchangerTotal += app(\App\Services\BybitService::class)->calculateTotalUsd($data);
                }

                if ($exchangerTotal > 0) {
                    $providerTotal += $exchangerTotal;
                }
            }

            if ($providerTotal > 0) {
                $providerTotals[$provider] = $providerTotal;
            }
        }

        // Показываем итоги по провайдерам
        foreach ($providerTotals as $provider => $total) {
            $percentage = $grandTotal > 0 ? round(($total / $grandTotal) * 100, 1) : 0;
            $message .= "🏢 <b>{$provider}:</b> {$this->formatUsd($total)} ({$percentage}%)\n";
        }

        $message .= "\n💰 <b><u>ИТОГО: {$this->formatUsd($grandTotal)}</u></b>\n";

        return $message;
    }

        private function getHeleketBalances(): array
    {
        $balances = [];
        $exchangers = ['obama', 'ural'];

        foreach ($exchangers as $exchanger) {
            $cfg = config("services.heleket.{$exchanger}");
            if (!$cfg) {
                continue;
            }

                        try {
                $url = $cfg['balance_url'];

                $body = json_encode([]);
                $sign = md5(base64_encode($body) . $cfg['api_key']);

                $response = \Illuminate\Support\Facades\Http::timeout(30)
                    ->withHeaders([
                        'merchant' => $cfg['merchant_uuid'],
                        'sign' => $sign,
                        'Content-Type' => 'application/json',
                    ])
                    ->post($url, []);

                if ($response->successful()) {
                    $data = $response->json();
                    $balances[$exchanger] = $this->normalizeHeleket($data);
                }
            } catch (\Exception $e) {
                $this->error("Ошибка получения балансов Heleket {$exchanger}: " . $e->getMessage());
            }
        }

        return $balances;
    }

    private function getRapiraBalances(): array
    {
        $balances = [];
        $exchangers = ['obama', 'ural'];

        foreach ($exchangers as $exchanger) {
            $cfg = config("services.rapira.{$exchanger}");
            if (!$cfg) {
                continue;
            }

                        try {
                $url = $cfg['balance_url'];

                $privateKey = $this->preparePrivateKey($cfg['private_key']);
                $jwt = $this->generateJwt([
                    'exp' => time() + 1800,
                    'jti' => bin2hex(random_bytes(12)),
                ], $privateKey);

                $tokenResponse = \Illuminate\Support\Facades\Http::timeout(30)->post('https://api.rapira.net/open/generate_jwt', [
                    'kid' => $cfg['uid'],
                    'jwt_token' => $jwt,
                ]);

                if (!$tokenResponse->successful()) {
                    continue;
                }

                $tokenData = $tokenResponse->json();
                $rapiraToken = $tokenData['token'] ?? null;

                if (!$rapiraToken) {
                    continue;
                }

                $balanceResponse = \Illuminate\Support\Facades\Http::timeout(30)->withHeaders([
                    'Authorization' => 'Bearer ' . $rapiraToken,
                ])->get($url);

                if ($balanceResponse->successful()) {
                    $data = $balanceResponse->json();
                    $balances[$exchanger] = $this->normalizeRapira($data);
                }
            } catch (\Exception $e) {
                $this->error("Ошибка получения балансов Rapira {$exchanger}: " . $e->getMessage());
            }
        }

        return $balances;
    }

    private function getBybitBalances(): array
    {
        $balances = [];
        $cfg = config("services.bybit.main");
        if (!$cfg) {
            return $balances;
        }

        try {
            $apiKey = $cfg['api_key'];
            $secretKey = $cfg['secret_key'];
            $testnet = $cfg['testnet'] ?? false;

            $baseUrl = $testnet ? 'https://api-testnet.bybit.com' : 'https://api.bybit.com';
            $timestamp = time() * 1000; // milliseconds

            // Получаем балансы кошелька
            $endpoint = '/v5/account/wallet-balance';
            $params = [
                'accountType' => 'UNIFIED'
                // Убираем 'coin' => 'USDT' чтобы получить все валюты
            ];

            $queryString = http_build_query($params);
            $signature = $this->generateBybitSignature($secretKey, $timestamp, $apiKey, 'GET', $endpoint, $queryString);

            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders([
                    'X-BAPI-API-KEY' => $apiKey,
                    'X-BAPI-SIGN' => $signature,
                    'X-BAPI-SIGN-TYPE' => '2',
                    'X-BAPI-TIMESTAMP' => $timestamp,
                    'X-BAPI-RECV-WINDOW' => '5000',
                ])
                ->get($baseUrl . $endpoint . '?' . $queryString);

            if ($response->successful()) {
                $data = $response->json();
                $balances['main'] = $this->normalizeBybit($data);
            }
        } catch (\Exception $e) {
            $this->error("Ошибка получения балансов Bybit: " . $e->getMessage());
        }

        return $balances;
    }

    private function generateBybitSignature(string $secretKey, int $timestamp, string $apiKey, string $method, string $endpoint, string $queryString = ''): string
    {
        $paramStr = $timestamp . $apiKey . '5000' . $queryString;
        return hash_hmac('sha256', $paramStr, $secretKey);
    }

    private function makeJwt(array $cfg, string $url): string
    {
        // Для Heleket используем api_key, для Rapira - uid
        $clientId = $cfg['api_key'] ?? $cfg['uid'] ?? null;

        if (!$clientId) {
            throw new \Exception('Не найден client_id (api_key или uid) в конфигурации');
        }

        $payload = [
            'iss' => $clientId,
            'aud' => $url,
            'iat' => time(),
            'exp' => time() + 300,
            'sub' => $clientId
        ];

        $privateKey = $this->preparePrivateKey($cfg['private_key']);
        return $this->generateJwt($payload, $privateKey);
    }

            private function preparePrivateKey(string $privateKey): string
    {
        // Если ключ уже в PEM формате, просто очищаем его
        if (strpos($privateKey, '-----BEGIN PRIVATE KEY-----') !== false) {
            $privateKey = str_replace(['-----BEGIN PRIVATE KEY-----', '-----END PRIVATE KEY-----', "\n", "\r"], '', $privateKey);
            $privateKey = chunk_split($privateKey, 64, "\n");
            return "-----BEGIN PRIVATE KEY-----\n{$privateKey}-----END PRIVATE KEY-----";
        }

        // Если ключ в RSA PEM формате
        if (strpos($privateKey, '-----BEGIN RSA PRIVATE KEY-----') !== false) {
            $privateKey = str_replace(['-----BEGIN RSA PRIVATE KEY-----', '-----END RSA PRIVATE KEY-----', "\n", "\r"], '', $privateKey);
            $privateKey = chunk_split($privateKey, 64, "\n");
            return "-----BEGIN RSA PRIVATE KEY-----\n{$privateKey}-----END RSA PRIVATE KEY-----";
        }

        // Если ключ в base64 формате, декодируем его
        $decodedKey = base64_decode($privateKey);
        if ($decodedKey === false) {
            throw new \Exception('Не удалось декодировать приватный ключ из base64');
        }

        // Убираем заголовки если они есть
        $decodedKey = str_replace(['-----BEGIN PRIVATE KEY-----', '-----END PRIVATE KEY-----', "\n", "\r"], '', $decodedKey);
        $decodedKey = chunk_split($decodedKey, 64, "\n");
        return "-----BEGIN PRIVATE KEY-----\n{$decodedKey}-----END PRIVATE KEY-----";
    }

    private function generateJwt(array $payload, string $privateKey): string
    {
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $headerEncoded = $this->urlsafeB64(json_encode($header));
        $payloadEncoded = $this->urlsafeB64(json_encode($payload));

        $signature = '';
        openssl_sign($headerEncoded . '.' . $payloadEncoded, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $signatureEncoded = $this->urlsafeB64($signature);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    private function urlsafeB64(string $input): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($input));
    }

    private function normalizeHeleket(array $raw): array
    {
        $normalized = [];

        if (isset($raw['result'][0]['balance']['merchant']) && is_array($raw['result'][0]['balance']['merchant'])) {
            foreach ($raw['result'][0]['balance']['merchant'] as $balance) {
                if (isset($balance['currency_code']) && isset($balance['balance'])) {
                    $normalized['merchant'][] = [
                        'code' => strtoupper($balance['currency_code']),
                        'amount' => (float)$balance['balance']
                    ];
                }
            }
        }

        if (isset($raw['result'][0]['balance']['user']) && is_array($raw['result'][0]['balance']['user'])) {
            foreach ($raw['result'][0]['balance']['user'] as $balance) {
                if (isset($balance['currency_code']) && isset($balance['balance'])) {
                    $normalized['user'][] = [
                        'code' => strtoupper($balance['currency_code']),
                        'amount' => (float)$balance['balance']
                    ];
                }
            }
        }

        return $normalized;
    }

    private function normalizeRapira(array $raw): array
    {
        $normalized = [];

        if (isset($raw['data']) && is_array($raw['data'])) {
            foreach ($raw['data'] as $balance) {
                if (isset($balance['currency']) && isset($balance['balance'])) {
                    $normalized[] = [
                        'code' => strtoupper($balance['currency']),
                        'amount' => (float)$balance['balance']
                    ];
                }
            }
        }

        return $normalized;
    }

    private function normalizeBybit(array $raw): array
    {
        $normalized = [];

        if (isset($raw['result']['list']) && is_array($raw['result']['list'])) {
            foreach ($raw['result']['list'] as $account) {
                if (isset($account['coin']) && is_array($account['coin'])) {
                    foreach ($account['coin'] as $coin) {
                        if (isset($coin['coin']) && isset($coin['walletBalance'])) {
                            $amount = (float)$coin['walletBalance'];
                            if ($amount > 0) {
                                $normalized[] = [
                                    'code' => strtoupper($coin['coin']),
                                    'amount' => $amount
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $normalized;
    }

    private function formatUsd(float $amount): string
    {
        if ($amount == 0) {
            return '$0.00';
        }
        if ($amount < 0.01 && $amount > 0) {
            return '$' . number_format($amount, 4);
        }
        return '$' . number_format($amount, 2);
    }
}
