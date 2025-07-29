<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BybitService
{
    private const CACHE_TTL = 300; // 5 минут
    private const BASE_URL = 'https://api.bybit.com/v5/market/tickers';

    // Маппинг валют к символам ByBit с указанием категории
    private const CURRENCY_SYMBOLS = [
        'BTC' => ['symbol' => 'BTCUSDT', 'category' => 'spot'],
        'ETH' => ['symbol' => 'ETHUSDT', 'category' => 'spot'],
        'USDT' => ['symbol' => 'USDTUSDT', 'category' => 'spot'], // Для USDT используем 1:1
        'USDC' => ['symbol' => 'USDCUSDT', 'category' => 'spot'],
        'DAI' => ['symbol' => 'DAIUSDT', 'category' => 'spot'],
        'BNB' => ['symbol' => 'BNBUSDT', 'category' => 'spot'],
        'DOGE' => ['symbol' => 'DOGEUSDT', 'category' => 'spot'],
        'SOL' => ['symbol' => 'SOLUSDT', 'category' => 'spot'],
        'TRX' => ['symbol' => 'TRXUSDT', 'category' => 'spot'],
        'SHIB' => ['symbol' => 'SHIBUSDT', 'category' => 'spot'],
        'LTC' => ['symbol' => 'LTCUSDT', 'category' => 'spot'],
        'TON' => ['symbol' => 'TONUSDT', 'category' => 'spot'],
        'BCH' => ['symbol' => 'BCHUSDT', 'category' => 'spot'],
        'XMR' => ['symbol' => 'XMRUSDT', 'category' => 'linear'], // Фьючерсы
        'AVAX' => ['symbol' => 'AVAXUSDT', 'category' => 'spot'],
        'DASH' => ['symbol' => 'DASHUSDT', 'category' => 'linear'], // Фьючерсы
        'POL' => ['symbol' => 'POLUSDT', 'category' => 'spot'],
        'RUB' => ['symbol' => 'RUBUSDT', 'category' => 'spot'],
        'OP' => ['symbol' => 'OPUSDT', 'category' => 'spot'],
        'NOT' => ['symbol' => 'NOTUSDT', 'category' => 'spot'],
        'ETC' => ['symbol' => 'ETCUSDT', 'category' => 'spot'],
        'DOGS' => ['symbol' => 'DOGSUSDT', 'category' => 'spot'],
        'TRUMP' => ['symbol' => 'TRUMPUSDT', 'category' => 'spot'],
    ];

    /**
     * Получить курс валюты к USDT
     */
    public function getCurrencyRate(string $symbol, string $category = 'spot'): float
    {
        $cacheKey = "bybit_rate_{$category}_{$symbol}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($symbol, $category) {
            return $this->fetchRateFromBybit($symbol, $category);
        });
    }

    /**
     * Получить курсы для нескольких валют
     */
    public function getCurrencyRates(array $symbols): array
    {
        $rates = [];

        foreach ($symbols as $symbol) {
            $rates[$symbol] = $this->getCurrencyRate($symbol);
        }

        return $rates;
    }

    /**
     * Конвертировать сумму в доллары
     */
    public function convertToUsd(string $currency, float $amount): float
    {
        // USDT, USDC, DAI считаем как 1:1 к доллару
        if (in_array(strtoupper($currency), ['USDT', 'USDC', 'DAI'])) {
            return $amount;
        }

        $upperCurrency = strtoupper($currency);

        // Получаем символ и категорию для ByBit API
        $symbolInfo = $this->getBybitSymbol($currency);

        if (!$symbolInfo) {
            Log::warning("BybitService: неизвестная валюта {$currency}");
            return 0.0;
        }

        // Для USDT возвращаем сумму как есть
        if ($symbolInfo['symbol'] === 'USDTUSDT') {
            return $amount;
        }

        // Для остальных валют получаем курс через USDT
        $rate = $this->getCurrencyRate($symbolInfo['symbol'], $symbolInfo['category']);
        return $amount * $rate;
    }

    /**
     * Получить символ для ByBit API
     */
    private function getBybitSymbol(string $currency): ?array
    {
        $upperCurrency = strtoupper($currency);
        return self::CURRENCY_SYMBOLS[$upperCurrency] ?? null;
    }

    /**
     * Получить курс с ByBit API
     */
    private function fetchRateFromBybit(string $symbol, string $category = 'spot'): float
    {
        try {
            $response = Http::timeout(10)->get(self::BASE_URL, [
                'category' => $category,
                'symbol' => $symbol
            ]);

            if (!$response->successful()) {
                Log::warning("BybitService: ошибка получения курса для {$symbol} ({$category})", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return 0.0;
            }

            $data = $response->json();

            if (isset($data['result']['list']) && !empty($data['result']['list'])) {
                $ticker = $data['result']['list'][0];
                $lastPrice = (float)($ticker['lastPrice'] ?? 0);

                Log::info("BybitService: получен курс для {$symbol} ({$category})", ['rate' => $lastPrice]);
                return $lastPrice;
            }

            Log::warning("BybitService: неожиданная структура ответа для {$symbol} ({$category})", ['data' => $data]);
            return 0.0;

        } catch (\Exception $e) {
            Log::error("BybitService: исключение при получении курса для {$symbol} ({$category})", [
                'error' => $e->getMessage()
            ]);
            return 0.0;
        }
    }

    /**
     * Получить общую сумму в долларах для списка балансов
     */
    public function calculateTotalUsd(array $balances): float
    {
        $total = 0.0;

        foreach ($balances as $balance) {
            $currency = $balance['code'] ?? '';
            $amount = $balance['amount'] ?? 0;

            if ($amount > 0) {
                $total += $this->convertToUsd($currency, $amount);
            }
        }

        return $total;
    }

    /**
     * Получить сумму в долларах для конкретного баланса
     */
    public function getBalanceUsd(array $balance): float
    {
        $currency = $balance['code'] ?? '';
        $amount = $balance['amount'] ?? 0;

        return $this->convertToUsd($currency, $amount);
    }
}
