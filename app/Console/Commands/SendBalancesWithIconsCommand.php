<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendBalancesWithIconsCommand extends Command
{
    protected $signature = 'telegram:send-balances-with-icons {--provider=} {--exchanger=}';
    protected $description = 'Отправка балансов в Telegram с информацией об иконках';

    private array $providers = [
        'heleket' => 'Heleket',
        'rapira' => 'Rapira'
    ];

    private array $exchangers = [
        'obama' => 'Obama',
        'ural' => 'Ural'
    ];

    public function handle()
    {
        $this->info('Начинаем сбор балансов обменников...');

        $balancesData = [];
        $provider = $this->option('provider');
        $exchanger = $this->option('exchanger');

        // Определяем какие провайдеры и обменники обрабатывать
        $providersToProcess = $provider ? [$provider] : array_keys($this->providers);
        $exchangersToProcess = $exchanger ? [$exchanger] : array_keys($this->exchangers);

        foreach ($providersToProcess as $prov) {
            if (!isset($this->providers[$prov])) {
                $this->error("Неизвестный провайдер: {$prov}");
                continue;
            }

            $balancesData[$this->providers[$prov]] = [];

            foreach ($exchangersToProcess as $exch) {
                if (!isset($this->exchangers[$exch])) {
                    $this->error("Неизвестный обменник: {$exch}");
                    continue;
                }

                $this->info("Получаем балансы для {$this->providers[$prov]} / {$this->exchangers[$exch]}...");

                try {
                    $balances = $this->getBalances($prov, $exch);

                    if ($balances !== null) {
                        $balancesData[$this->providers[$prov]][$this->exchangers[$exch]] = $balances;
                        $this->info("✓ Балансы получены");
                    } else {
                        $this->warn("⚠ Не удалось получить балансы");
                    }
                } catch (\Exception $e) {
                    $this->error("✗ Ошибка получения балансов: " . $e->getMessage());
                }
            }
        }

        if (empty($balancesData)) {
            $this->error('Не удалось получить данные балансов');
            return 1;
        }

        $this->info('Отправляем данные в Telegram...');

        $telegramService = app(TelegramService::class);
        $message = $this->formatBalancesMessageWithIcons($balancesData);

        if ($telegramService->sendMessage($message)) {
            $this->info('✓ Данные успешно отправлены в Telegram');
        } else {
            $this->error('❌ Ошибка отправки в Telegram');
            return 1;
        }

        return 0;
    }

    /**
     * Получить балансы для конкретного провайдера и обменника
     */
    private function getBalances(string $provider, string $exchanger): ?array
    {
        $cfg = config("services.{$provider}.{$exchanger}");
        if (!$cfg) {
            Log::error("SendBalancesWithIconsCommand: не найдена конфигурация", [
                'provider' => $provider,
                'exchanger' => $exchanger
            ]);
            return null;
        }

        $url = $cfg['balance_url'];

        try {
            if ($provider === 'heleket') {
                return $this->getHeleketBalances($cfg, $url);
            } else {
                return $this->getRapiraBalances($cfg, $url);
            }
        } catch (\Exception $e) {
            Log::error("SendBalancesWithIconsCommand: ошибка получения балансов", [
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
        $privateKey = $this->preparePrivateKey($cfg['private_key']);
        $payload = [
            'iss' => $cfg['client_id'],
            'aud' => $cfg['audience'],
            'iat' => time(),
            'exp' => time() + 3600
        ];

        $jwt = $this->makeJwt($payload, $privateKey);

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . $jwt,
            'Content-Type' => 'application/json'
        ])->get($url);

        if (!$response->successful()) {
            throw new \Exception("HTTP error: " . $response->status());
        }

        $data = $response->json();
        return $this->normalizeHeleket($data);
    }

    /**
     * Получить балансы Rapira
     */
    private function getRapiraBalances(array $cfg, string $url): array
    {
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . $cfg['api_key'],
            'Content-Type' => 'application/json'
        ])->get($url);

        if (!$response->successful()) {
            throw new \Exception("HTTP error: " . $response->status());
        }

        $data = $response->json();
        return $this->normalizeRapira($data);
    }

    /**
     * Подготовить приватный ключ
     */
    private function preparePrivateKey(string $privateKey): string
    {
        $privateKey = str_replace(['-----BEGIN PRIVATE KEY-----', '-----END PRIVATE KEY-----', "\n", "\r"], '', $privateKey);
        $privateKey = "-----BEGIN PRIVATE KEY-----\n" . chunk_split($privateKey, 64, "\n") . "-----END PRIVATE KEY-----";
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

        $signature = '';
        openssl_sign($headerEncoded . '.' . $payloadEncoded, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $signatureEncoded = $this->urlsafeB64($signature);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
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

        if (isset($raw['merchant']) && is_array($raw['merchant'])) {
            $result['merchant'] = [];
            foreach ($raw['merchant'] as $currency => $amount) {
                if (is_numeric($amount) && $amount != 0) {
                    $result['merchant'][] = [
                        'code' => strtoupper($currency),
                        'amount' => (float)$amount
                    ];
                }
            }
        }

        if (isset($raw['user']) && is_array($raw['user'])) {
            $result['user'] = [];
            foreach ($raw['user'] as $currency => $amount) {
                if (is_numeric($amount) && $amount != 0) {
                    $result['user'][] = [
                        'code' => strtoupper($currency),
                        'amount' => (float)$amount
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Нормализовать данные Rapira
     */
    private function normalizeRapira(array $raw): array
    {
        $result = [];

        if (isset($raw['data']) && is_array($raw['data'])) {
            foreach ($raw['data'] as $currency => $amount) {
                if (is_numeric($amount) && $amount != 0) {
                    $result[] = [
                        'code' => strtoupper($currency),
                        'amount' => (float)$amount
                    ];
                }
            }
        }

        return $result;
    }

    private function formatBalancesMessageWithIcons(array $balancesData): string
    {
        $date = now()->format('d.m.Y H:i');
        $message = "💰 <b>Балансы обменников</b> ({$date})\n\n";

        $message .= "🎨 <b>Иконки валют:</b>\n";
        $message .= "Все валюты имеют красивые SVG иконки в папке /public/images/coins/\n\n";

        $hasData = false;
        $providerTotals = [];
        $grandTotal = 0.0;
        $usedCurrencies = [];

        // Определяем порядок провайдеров и обменников
        $providerOrder = ['Heleket', 'Rapira'];
        $exchangerOrder = ['Obama', 'Ural'];

        foreach ($providerOrder as $provider) {
            if (!isset($balancesData[$provider]) || empty($balancesData[$provider])) {
                continue;
            }

            $providerIcon = $this->getProviderIcon($provider);
            $message .= "{$providerIcon} <b>{$provider}</b>\n";
            $providerTotal = 0.0;

            foreach ($exchangerOrder as $exchanger) {
                if (!isset($balancesData[$provider][$exchanger]) || empty($balancesData[$provider][$exchanger])) {
                    continue;
                }

                $exchangerIcon = $this->getExchangerIcon($exchanger);
                $message .= "  └ {$exchangerIcon} <b>{$exchanger}</b>\n";
                $hasData = true;
                $exchangerTotal = 0.0;

                if (isset($balancesData[$provider][$exchanger]['merchant']) && is_array($balancesData[$provider][$exchanger]['merchant']) && !empty($balancesData[$provider][$exchanger]['merchant'])) {
                    $merchantTotal = app(\App\Services\BybitService::class)->calculateTotalUsd($balancesData[$provider][$exchanger]['merchant']);
                    $exchangerTotal += $merchantTotal;
                    $message .= "    💼 <b>Мерчант</b> ({$this->formatUsd($merchantTotal)}):\n";
                    foreach ($balancesData[$provider][$exchanger]['merchant'] as $balance) {
                        if ($balance['amount'] > 0) {
                            $amount = number_format($balance['amount'], 8);
                            $usdAmount = app(\App\Services\BybitService::class)->getBalanceUsd($balance);
                            $currencyIcon = $this->getCurrencyIcon($balance['code']);
                            $iconStatus = $this->getIconStatus($balance['code']);
                            $message .= "      {$currencyIcon} {$balance['code']}: {$amount} ({$this->formatUsd($usdAmount)}) {$iconStatus}\n";
                            $usedCurrencies[$balance['code']] = true;
                        }
                    }
                }

                if (isset($balancesData[$provider][$exchanger]['user']) && is_array($balancesData[$provider][$exchanger]['user']) && !empty($balancesData[$provider][$exchanger]['user'])) {
                    $userTotal = app(\App\Services\BybitService::class)->calculateTotalUsd($balancesData[$provider][$exchanger]['user']);
                    $exchangerTotal += $userTotal;
                    $message .= "    👤 <b>Пользователь</b> ({$this->formatUsd($userTotal)}):\n";
                    foreach ($balancesData[$provider][$exchanger]['user'] as $balance) {
                        if ($balance['amount'] > 0) {
                            $amount = number_format($balance['amount'], 8);
                            $usdAmount = app(\App\Services\BybitService::class)->getBalanceUsd($balance);
                            $currencyIcon = $this->getCurrencyIcon($balance['code']);
                            $iconStatus = $this->getIconStatus($balance['code']);
                            $message .= "      {$currencyIcon} {$balance['code']}: {$amount} ({$this->formatUsd($usdAmount)}) {$iconStatus}\n";
                            $usedCurrencies[$balance['code']] = true;
                        }
                    }
                }

                // Для обычных балансов (не Heleket)
                if (!isset($balancesData[$provider][$exchanger]['merchant']) && !isset($balancesData[$provider][$exchanger]['user']) && is_array($balancesData[$provider][$exchanger]) && !empty($balancesData[$provider][$exchanger])) {
                    $exchangerTotal = app(\App\Services\BybitService::class)->calculateTotalUsd($balancesData[$provider][$exchanger]);
                    $message .= "    💰 <b>Общий баланс</b> ({$this->formatUsd($exchangerTotal)}):\n";
                    foreach ($balancesData[$provider][$exchanger] as $balance) {
                        if ($balance['amount'] > 0) {
                            $amount = number_format($balance['amount'], 8);
                            $usdAmount = app(\App\Services\BybitService::class)->getBalanceUsd($balance);
                            $currencyIcon = $this->getCurrencyIcon($balance['code']);
                            $iconStatus = $this->getIconStatus($balance['code']);
                            $message .= "      {$currencyIcon} {$balance['code']}: {$amount} ({$this->formatUsd($usdAmount)}) {$iconStatus}\n";
                            $usedCurrencies[$balance['code']] = true;
                        }
                    }
                }

                // Итого по обменнику
                if ($exchangerTotal > 0) {
                    $message .= "    📈 <b>Итого по {$exchanger}</b>: {$this->formatUsd($exchangerTotal)}\n";
                    $providerTotal += $exchangerTotal;
                }

                $message .= "\n";
            }

            // Итого по провайдеру
            if ($providerTotal > 0) {
                $providerTotals[$provider] = $providerTotal;
                $grandTotal += $providerTotal;
                $message .= "🏆 <b>Итого по {$provider}</b>: {$this->formatUsd($providerTotal)}\n\n";
            }
        }

        if (!$hasData) {
            $message .= "❌ Не удалось получить данные балансов\n";
        } else {
            // Детализация по провайдерам
            $message .= "📋 <b>Детализация по провайдерам:</b>\n";
            foreach ($providerOrder as $provider) {
                if (isset($providerTotals[$provider])) {
                    $total = $providerTotals[$provider];
                    $percentage = $grandTotal > 0 ? round(($total / $grandTotal) * 100, 1) : 0;
                    $providerIcon = $this->getProviderIcon($provider);
                    $message .= "  {$providerIcon} {$provider}: {$this->formatUsd($total)} ({$percentage}%)\n";
                }
            }

            // Общий итог
            $message .= "\n💵 <b>ОБЩИЙ БАЛАНС: {$this->formatUsd($grandTotal)}</b>\n";

            // Информация об иконках
            $message .= "\n🎨 <b>Иконки валют:</b>\n";
            $message .= "  • Всего валют в сообщении: " . count($usedCurrencies) . "\n";
            $message .= "  • Иконки доступны в: /public/images/coins/\n";
            $message .= "  • Формат: SVG файлы\n";
        }

        return $message;
    }

    private function getProviderIcon(string $provider): string
    {
        return match (strtolower($provider)) {
            'heleket' => '🟢',
            'rapira' => '🔵',
            default => '📊'
        };
    }

    private function getExchangerIcon(string $exchanger): string
    {
        return match (strtolower($exchanger)) {
            'obama' => '👨‍💼',
            'ural' => '🏔️',
            default => '🏢'
        };
    }

    private function getCurrencyIcon(string $currency): string
    {
        return match (strtoupper($currency)) {
            'BTC' => '₿',
            'ETH' => 'Ξ',
            'USDT', 'USDC', 'DAI' => '💵',
            'BNB' => '🟡',
            'DOGE', 'SHIB', 'DOGS' => '🐕',
            'SOL' => '☀️',
            'TRX' => '⚡',
            'LTC' => 'Ł',
            'TON', 'DASH' => '💎',
            'BCH' => '₿',
            'XMR' => '🔒',
            'AVAX' => '❄️',
            'POL' => '🔷',
            'RUB' => '₽',
            'OP' => '🔵',
            'NOT' => '📝',
            'ETC' => '🔶',
            default => '🪙'
        };
    }

    private function getIconStatus(string $currency): string
    {
        $upperCurrency = strtoupper($currency);
        $iconPath = "images/coins/{$upperCurrency}.svg";
        $fullPath = public_path($iconPath);

        return file_exists($fullPath) ? '✅' : '❌';
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
