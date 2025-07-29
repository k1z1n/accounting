<?php

namespace App\Console\Commands;

use App\Services\BybitService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestBybitRatesCommand extends Command
{
    protected $signature = 'test:bybit-rates {symbol?} {--search=}';
    protected $description = 'Тестирование получения курсов валют через ByBit';

    public function handle()
    {
        if ($search = $this->option('search')) {
            $this->searchSymbols($search);
            return 0;
        }

        $symbol = $this->argument('symbol') ?? 'BTCUSDT';

        $this->info("Тестирование получения курса для {$symbol}");

        $bybitService = app(BybitService::class);

        try {
            // Определяем категорию для символа
            $category = $this->getCategoryForSymbol($symbol);
            $rate = $bybitService->getCurrencyRate($symbol, $category);
            $this->info("Курс {$symbol} ({$category}): {$rate}");

            // Тестируем конвертацию
            $testAmount = 1.0;
            $currency = str_replace('USDT', '', $symbol);
            $usdAmount = $bybitService->convertToUsd($currency, $testAmount);
            $this->info("Конвертация {$testAmount} {$currency} = {$usdAmount}$");

        } catch (\Exception $e) {
            $this->error("Ошибка: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function searchSymbols(string $query): void
    {
        $this->info("Поиск символов содержащих '{$query}' в ByBit...");

        try {
            $response = Http::timeout(10)->get('https://api.bybit.com/v5/market/tickers', [
                'category' => 'spot'
            ]);

            if (!$response->successful()) {
                $this->error("Ошибка получения данных: " . $response->status());
                return;
            }

            $data = $response->json();
            $symbols = $data['result']['list'] ?? [];

            $found = [];
            foreach ($symbols as $ticker) {
                $symbol = $ticker['symbol'] ?? '';
                if (stripos($symbol, $query) !== false) {
                    $found[] = $symbol;
                }
            }

            if (empty($found)) {
                $this->warn("Символы с '{$query}' не найдены");
            } else {
                $this->info("Найдены символы:");
                foreach (array_slice($found, 0, 20) as $symbol) {
                    $this->line("  - {$symbol}");
                }
                if (count($found) > 20) {
                    $this->line("  ... и еще " . (count($found) - 20) . " символов");
                }
            }

        } catch (\Exception $e) {
            $this->error("Ошибка поиска: " . $e->getMessage());
        }
    }

    private function getCategoryForSymbol(string $symbol): string
    {
        // Определяем категорию на основе символа
        $linearSymbols = ['XMRUSDT', 'DASHUSDT'];

        if (in_array($symbol, $linearSymbols)) {
            return 'linear';
        }

        return 'spot';
    }
}
