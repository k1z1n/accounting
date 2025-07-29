<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckCurrencyIconsCommand extends Command
{
    protected $signature = 'icons:check-currencies {currency?}';
    protected $description = 'Проверка доступных иконок валют';

    public function handle()
    {
        $currency = $this->argument('currency');

        if ($currency) {
            $this->checkSingleCurrency($currency);
        } else {
            $this->checkAllCurrencies();
        }

        return 0;
    }

    private function checkSingleCurrency(string $currency): void
    {
        $upperCurrency = strtoupper($currency);
        $iconPath = "images/coins/{$upperCurrency}.svg";
        $fullPath = public_path($iconPath);

        $this->info("Проверка иконки для {$upperCurrency}:");

        if (file_exists($fullPath)) {
            $this->info("✅ Иконка найдена: {$iconPath}");
            $this->info("📁 Полный путь: {$fullPath}");
            $this->info("📏 Размер: " . filesize($fullPath) . " байт");
        } else {
            $this->error("❌ Иконка не найдена: {$iconPath}");
        }
    }

    private function checkAllCurrencies(): void
    {
        $this->info('🔍 Проверка всех иконок валют');
        $this->info('=============================');

        $coinsDir = public_path('images/coins');

        if (!is_dir($coinsDir)) {
            $this->error("❌ Папка {$coinsDir} не найдена");
            return;
        }

        $files = glob($coinsDir . '/*.svg');
        $totalFiles = count($files);

        $this->info("📁 Найдено {$totalFiles} иконок валют");

        $currencies = [];
        foreach ($files as $file) {
            $filename = basename($file, '.svg');
            $currencies[] = $filename;
        }

        sort($currencies);

        $this->info("\n📋 Доступные валюты:");
        foreach ($currencies as $currency) {
            $this->line("  • {$currency}");
        }

        // Проверяем популярные валюты
        $popularCurrencies = ['BTC', 'ETH', 'USDT', 'USDC', 'DAI', 'BNB', 'DOGE', 'SOL', 'TRX', 'SHIB', 'LTC', 'TON', 'BCH', 'XMR', 'AVAX', 'DASH', 'POL', 'RUB', 'OP', 'NOT', 'ETC', 'DOGS'];

        $this->info("\n🎯 Проверка популярных валют:");
        foreach ($popularCurrencies as $currency) {
            $iconPath = "images/coins/{$currency}.svg";
            $fullPath = public_path($iconPath);

            $status = file_exists($fullPath) ? '✅' : '❌';
            $this->line("  {$status} {$currency}");
        }
    }
}
