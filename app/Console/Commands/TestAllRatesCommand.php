<?php

namespace App\Console\Commands;

use App\Services\BybitService;
use Illuminate\Console\Command;

class TestAllRatesCommand extends Command
{
    protected $signature = 'test:all-rates';
    protected $description = 'Комплексное тестирование всех курсов валют';

    private array $testCurrencies = [
        'BTC' => 0.001,
        'ETH' => 0.01,
        'USDT' => 100,
        'USDC' => 100,
        'DAI' => 100,
        'BNB' => 0.1,
        'DOGE' => 1000,
        'SOL' => 1,
        'TRX' => 1000,
        'SHIB' => 1000000,
        'LTC' => 1,
        'TON' => 100,
        'BCH' => 0.1,
        'XMR' => 1,
        'AVAX' => 1,
        'DASH' => 1,
        'POL' => 10,
        'OP' => 1,
        'NOT' => 100,
        'ETC' => 1,
        'DOGS' => 1000000,
    ];

    public function handle()
    {
        $this->info('🧪 Комплексное тестирование курсов валют');
        $this->info('=====================================');

        $bybitService = app(BybitService::class);
        $results = [];
        $totalUsd = 0;

        foreach ($this->testCurrencies as $currency => $amount) {
            try {
                $usdAmount = $bybitService->convertToUsd($currency, $amount);
                $results[$currency] = [
                    'amount' => $amount,
                    'usd' => $usdAmount,
                    'rate' => $amount > 0 ? $usdAmount / $amount : 0
                ];
                $totalUsd += $usdAmount;

                $status = $usdAmount > 0 ? '✅' : '❌';
                $this->line("{$status} {$currency}: {$amount} → {$this->formatUsd($usdAmount)}");

            } catch (\Exception $e) {
                $results[$currency] = [
                    'amount' => $amount,
                    'usd' => 0,
                    'rate' => 0,
                    'error' => $e->getMessage()
                ];
                $this->line("❌ {$currency}: Ошибка - {$e->getMessage()}");
            }
        }

        $this->info("\n📊 Результаты тестирования:");
        $this->info("==========================");
        $this->info("Общая стоимость тестового портфеля: {$this->formatUsd($totalUsd)}");

        $workingCurrencies = count(array_filter($results, fn($r) => $r['usd'] > 0));
        $totalCurrencies = count($results);

        $this->info("Работающих валют: {$workingCurrencies}/{$totalCurrencies}");

        if ($workingCurrencies < $totalCurrencies) {
            $this->warn("\n⚠️ Проблемные валюты:");
            foreach ($results as $currency => $result) {
                if ($result['usd'] == 0) {
                    $error = $result['error'] ?? 'Неизвестная ошибка';
                    $this->line("  • {$currency}: {$error}");
                }
            }
        }

        $this->info("\n✅ Тестирование завершено!");
        return 0;
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
