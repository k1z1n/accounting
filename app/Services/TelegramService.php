<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $botToken;
    private string $chatId;
    private BybitService $bybitService;

    public function __construct(BybitService $bybitService)
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->chatId = config('services.telegram.chat_id');
        $this->bybitService = $bybitService;
    }

    /**
     * Отправить сообщение в Telegram
     */
    public function sendMessage(string $message, bool $parseMode = true): bool
    {
        if (empty($this->botToken) || empty($this->chatId)) {
            Log::error('TelegramService: не настроены bot_token или chat_id');
            return false;
        }

        try {
            $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

            $data = [
                'chat_id' => $this->chatId,
                'text' => $message,
            ];

            if ($parseMode) {
                $data['parse_mode'] = 'HTML';
            }

            Log::info('TelegramService: отправка сообщения', [
                'url' => $url,
                'chat_id' => $this->chatId,
                'message_length' => strlen($message),
                'parse_mode' => $parseMode ? 'HTML' : 'none',
                'message_preview' => substr($message, 0, 200) . (strlen($message) > 200 ? '...' : '')
            ]);

            $response = Http::timeout(10)->post($url, $data);

            Log::info('TelegramService: ответ от Telegram API', [
                'status' => $response->status(),
                'body' => $response->body(),
                'successful' => $response->successful()
            ]);

            if (!$response->successful()) {
                Log::error('TelegramService: ошибка отправки сообщения', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }

            $responseData = $response->json();
            Log::info('TelegramService: сообщение отправлено успешно', [
                'message_id' => $responseData['result']['message_id'] ?? 'unknown',
                'chat_id' => $responseData['result']['chat']['id'] ?? 'unknown'
            ]);
            return true;

        } catch (\Exception $e) {
            Log::error('TelegramService: исключение при отправке', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Отправить данные о балансах обменников
     */
    public function sendExchangerBalances(array $balancesData): bool
    {
        Log::info('TelegramService: отправка балансов', ['data' => $balancesData]);

        if (empty($balancesData)) {
            Log::warning('TelegramService: пустые данные балансов');
            return $this->sendMessage("⚠️ <b>Балансы обменников</b> (" . now()->format('d.m.Y H:i') . ")\n\n❌ Не удалось получить данные балансов");
        }

        $message = $this->formatBalancesMessage($balancesData);
        Log::info('TelegramService: сформированное сообщение', ['message_length' => strlen($message)]);

        return $this->sendMessage($message);
    }

    /**
     * Форматировать сообщение с балансами
     */
    private function formatBalancesMessage(array $balancesData): string
    {
        Log::info('TelegramService: начало форматирования сообщения', [
            'providers_count' => count($balancesData),
            'providers' => array_keys($balancesData)
        ]);

        $date = now()->format('d.m.Y');
        $message = "";

        $hasData = false;
        $providerTotals = [];
        $grandTotal = 0.0;

        // Определяем порядок провайдеров и обменников
        $providerOrder = ['Heleket', 'Rapira', 'Bybit'];
        $exchangerOrder = ['Obama', 'Ural', 'Main'];

        foreach ($providerOrder as $provider) {
            if (!isset($balancesData[$provider]) || empty($balancesData[$provider])) {
                Log::info("TelegramService: пропускаем провайдера {$provider} - нет данных");
                continue;
            }

            Log::info("TelegramService: обрабатываем провайдера {$provider}", [
                'exchangers' => array_keys($balancesData[$provider])
            ]);

            foreach ($exchangerOrder as $exchanger) {
                if (!isset($balancesData[$provider][$exchanger]) || empty($balancesData[$provider][$exchanger])) {
                    continue;
                }

                $message .= "[{$provider} {$exchanger}] Остаток на {$date}\n\n";
                $hasData = true;
                $exchangerTotal = 0.0;

                if (isset($balancesData[$provider][$exchanger]['merchant']) && is_array($balancesData[$provider][$exchanger]['merchant']) && !empty($balancesData[$provider][$exchanger]['merchant'])) {
                    $merchantTotal = $this->bybitService->calculateTotalUsd($balancesData[$provider][$exchanger]['merchant']);
                    $exchangerTotal += $merchantTotal;
                    $message .= "Мерчант ({$this->formatUsd($merchantTotal)})\n";
                    foreach ($balancesData[$provider][$exchanger]['merchant'] as $balance) {
                        if ($balance['amount'] > 0) {
                            $amount = number_format($balance['amount'], 10);
                            $usdAmount = $this->bybitService->getBalanceUsd($balance);
                            $message .= "[{$balance['code']}] {$amount} ({$this->formatUsd($usdAmount)})\n";
                        }
                    }
                    $message .= "\n";
                }

                if (isset($balancesData[$provider][$exchanger]['user']) && is_array($balancesData[$provider][$exchanger]['user']) && !empty($balancesData[$provider][$exchanger]['user'])) {
                    $userTotal = $this->bybitService->calculateTotalUsd($balancesData[$provider][$exchanger]['user']);
                    $exchangerTotal += $userTotal;
                    $message .= "Пользователь ({$this->formatUsd($userTotal)})\n";
                    foreach ($balancesData[$provider][$exchanger]['user'] as $balance) {
                        if ($balance['amount'] > 0) {
                            $amount = number_format($balance['amount'], 10);
                            $usdAmount = $this->bybitService->getBalanceUsd($balance);
                            $message .= "[{$balance['code']}] {$amount} ({$this->formatUsd($usdAmount)})\n";
                        }
                    }
                    $message .= "\n";
                }

                // Для обычных балансов (не Heleket)
                if (!isset($balancesData[$provider][$exchanger]['merchant']) && !isset($balancesData[$provider][$exchanger]['user']) && is_array($balancesData[$provider][$exchanger]) && !empty($balancesData[$provider][$exchanger])) {
                    $exchangerTotal = $this->bybitService->calculateTotalUsd($balancesData[$provider][$exchanger]);

                    // Показываем общий баланс только для Heleket и Rapira, но не для Bybit
                    if ($provider !== 'Bybit') {
                        $message .= "Общий баланс ({$this->formatUsd($exchangerTotal)})\n";
                    }

                    foreach ($balancesData[$provider][$exchanger] as $balance) {
                        if ($balance['amount'] > 0) {
                            $amount = number_format($balance['amount'], 10);
                            $usdAmount = $this->bybitService->getBalanceUsd($balance);
                            $message .= "[{$balance['code']}] {$amount} ({$this->formatUsd($usdAmount)})\n";
                        }
                    }
                    $message .= "\n";
                }

                // Итого по обменнику
                if ($exchangerTotal > 0) {
                    $message .= "<b><u>Всего: {$this->formatUsd($exchangerTotal)}</u></b>\n\n";
                    $providerTotals[$provider] = ($providerTotals[$provider] ?? 0) + $exchangerTotal;
                    $grandTotal += $exchangerTotal;
                }
            }
        }

        if (!$hasData) {
            $message .= "❌ Не удалось получить данные балансов\n";
        } else {
            // Общий итог
            $message .= "💵 <b><u>ОБЩИЙ БАЛАНС: {$this->formatUsd($grandTotal)}</u></b>\n";

            // Рассчитываем распределение
            // $distribution = $this->calculateDistribution($balancesData, $grandTotal);

                        // Дополнительная информация
            // $message .= "\n📊 <b>Статистика:</b>\n";
            // $message .= "  • Провайдеров: " . count($providerTotals) . "\n";
            // $message .= "  • Обменников: " . array_sum(array_map('count', $balancesData)) . "\n";
            // $message .= "  • Валют: " . $this->countUniqueCurrencies($balancesData) . "\n";

            // Распределение по провайдерам
            // if (!empty($distribution['providers'])) {
            //     $message .= "\n🏢 <b>Распределение по провайдерам:</b>\n";
            //     foreach ($distribution['providers'] as $provider => $data) {
            //         $message .= "  • {$provider}: {$this->formatUsd($data['total'])} ({$data['percentage']}%)\n";
            //     }
            // }

            // Распределение по обменникам
            // if (!empty($distribution['exchangers'])) {
            //     $message .= "\n🏦 <b>Распределение по обменникам:</b>\n";
            //     foreach ($distribution['exchangers'] as $exchanger => $data) {
            //         $message .= "  • {$exchanger}: {$this->formatUsd($data['total'])} ({$data['percentage']}%)\n";
            //     }
            // }
        }

        return $message;
    }

    /**
     * Получить иконку провайдера
     */
    private function getProviderIcon(string $provider): string
    {
        return match (strtolower($provider)) {
            'heleket' => '🟢',
            'rapira' => '🔵',
            'bybit' => '🟡',
            default => '��'
        };
    }

    /**
     * Получить иконку обменника
     */
    private function getExchangerIcon(string $exchanger): string
    {
        return match (strtolower($exchanger)) {
            'obama' => '👨‍💼',
            'ural' => '🏔️',
            default => '🏢'
        };
    }

    /**
     * Получить иконку валюты
     */
    private function getCurrencyIcon(string $currency): string
    {
        $upperCurrency = strtoupper($currency);

        // Проверяем, есть ли иконка в папке coins
        $iconPath = "images/coins/{$upperCurrency}.svg";
        $fullPath = public_path($iconPath);

        if (file_exists($fullPath)) {
            // Возвращаем эмодзи-заглушку, так как в текстовых сообщениях нельзя вставлять изображения
            return $this->getCurrencyEmoji($currency);
        }

        // Fallback на эмодзи, если иконка не найдена
        return $this->getCurrencyEmoji($currency);
    }

    /**
     * Получить статус иконки валюты
     */
    private function getIconStatus(string $currency): string
    {
        $upperCurrency = strtoupper($currency);
        $iconPath = "images/coins/{$upperCurrency}.svg";
        $fullPath = public_path($iconPath);

        if (file_exists($fullPath)) {
            return '✅'; // Иконка найдена
        }
        return '❌'; // Иконка не найдена
    }

    /**
     * Получить эмодзи для валюты
     */
    private function getCurrencyEmoji(string $currency): string
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

    /**
     * Получить путь к иконке валюты
     */
    private function getCurrencyIconPath(string $currency): ?string
    {
        $upperCurrency = strtoupper($currency);
        $iconPath = "images/coins/{$upperCurrency}.svg";
        $fullPath = public_path($iconPath);

        return file_exists($fullPath) ? $iconPath : null;
    }

    /**
     * Подсчитать уникальные валюты
     */
    private function countUniqueCurrencies(array $balancesData): int
    {
        $currencies = [];

        foreach ($balancesData as $provider => $exchangers) {
            foreach ($exchangers as $exchanger => $data) {
                if (isset($data['merchant']) && is_array($data['merchant'])) {
                    foreach ($data['merchant'] as $balance) {
                        if ($balance['amount'] > 0) {
                            $currencies[$balance['code']] = true;
                        }
                    }
                }
                if (isset($data['user']) && is_array($data['user'])) {
                    foreach ($data['user'] as $balance) {
                        if ($balance['amount'] > 0) {
                            $currencies[$balance['code']] = true;
                        }
                    }
                }
                if (!isset($data['merchant']) && !isset($data['user']) && is_array($data)) {
                    foreach ($data as $balance) {
                        if ($balance['amount'] > 0) {
                            $currencies[$balance['code']] = true;
                        }
                    }
                }
            }
        }

        return count($currencies);
    }

    /**
     * Рассчитать процентное распределение средств
     */
    private function calculateDistribution(array $balancesData, float $grandTotal): array
    {
        $distribution = [
            'providers' => [],
            'exchangers' => []
        ];

        $providerTotals = [];
        $exchangerTotals = [];

        foreach ($balancesData as $provider => $exchangers) {
            $providerTotal = 0;

            foreach ($exchangers as $exchanger => $data) {
                $exchangerTotal = 0;

                if (isset($data['merchant']) && is_array($data['merchant'])) {
                    $exchangerTotal += $this->bybitService->calculateTotalUsd($data['merchant']);
                }
                if (isset($data['user']) && is_array($data['user'])) {
                    $exchangerTotal += $this->bybitService->calculateTotalUsd($data['user']);
                }
                if (!isset($data['merchant']) && !isset($data['user']) && is_array($data)) {
                    $exchangerTotal += $this->bybitService->calculateTotalUsd($data);
                }

                if ($exchangerTotal > 0) {
                    $exchangerTotals["{$provider} {$exchanger}"] = $exchangerTotal;
                    $providerTotal += $exchangerTotal;
                }
            }

            if ($providerTotal > 0) {
                $providerTotals[$provider] = $providerTotal;
            }
        }

        // Рассчитываем проценты для провайдеров
        foreach ($providerTotals as $provider => $total) {
            $percentage = $grandTotal > 0 ? ($total / $grandTotal) * 100 : 0;
            $distribution['providers'][$provider] = [
                'total' => $total,
                'percentage' => round($percentage, 1)
            ];
        }

        // Рассчитываем проценты для обменников
        foreach ($exchangerTotals as $exchanger => $total) {
            $percentage = $grandTotal > 0 ? ($total / $grandTotal) * 100 : 0;
            $distribution['exchangers'][$exchanger] = [
                'total' => $total,
                'percentage' => round($percentage, 1)
            ];
        }

        return $distribution;
    }

    /**
     * Форматировать сумму в долларах
     */
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
