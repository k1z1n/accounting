<?php

namespace App\Console\Commands;

use App\Services\BybitService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckRatesAccuracyCommand extends Command
{
    protected $signature = 'rates:check-accuracy';
    protected $description = 'Проверка точности курсов валют';

    public function handle()
    {
        $this->info('🔍 Проверка точности курсов валют');
        $this->info('================================');

        $bybitService = app(BybitService::class);

        // Тестируем несколько ключевых валют
        $testPairs = [
            'BTC' => 'bitcoin',
            'ETH' => 'ethereum',
            'XMR' => 'monero',
            'DASH' => 'dash',
            'TON' => 'the-open-network',
            'DOGE' => 'dogecoin'
        ];

        foreach ($testPairs as $symbol => $coingeckoId) {
            $this->checkRateAccuracy($bybitService, $symbol, $coingeckoId);
        }

        $this->info("\n✅ Проверка завершена!");
        return 0;
    }

    private function checkRateAccuracy(BybitService $bybitService, string $symbol, string $coingeckoId): void
    {
        $this->info("\n📊 Проверка {$symbol}:");

        try {
            // Получаем курс через ByBit
            $bybitRate = $bybitService->convertToUsd($symbol, 1.0);

            // Получаем курс через CoinGecko для сравнения
            $coingeckoRate = $this->getCoinGeckoRate($coingeckoId);

            if ($coingeckoRate > 0) {
                $difference = abs($bybitRate - $coingeckoRate);
                $percentage = ($difference / $coingeckoRate) * 100;

                $this->line("  ByBit:     \${$bybitRate}");
                $this->line("  CoinGecko: \${$coingeckoRate}");
                $this->line("  Разница:   \${$difference} ({$percentage}%)");

                if ($percentage < 1) {
                    $this->line("  ✅ Отличная точность");
                } elseif ($percentage < 5) {
                    $this->line("  ⚠️ Хорошая точность");
                } else {
                    $this->line("  ❌ Большая разница");
                }
            } else {
                $this->line("  ByBit: \${$bybitRate}");
                $this->line("  CoinGecko: Не удалось получить");
            }

        } catch (\Exception $e) {
            $this->error("  Ошибка: {$e->getMessage()}");
        }
    }

    private function getCoinGeckoRate(string $id): float
    {
        try {
            $response = Http::timeout(10)->get('https://api.coingecko.com/api/v3/simple/price', [
                'ids' => $id,
                'vs_currencies' => 'usd'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data[$id]['usd'] ?? 0;
            }
        } catch (\Exception $e) {
            // Игнорируем ошибки CoinGecko
        }

        return 0;
    }
}
