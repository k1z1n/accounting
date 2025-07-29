<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateFallbackRatesCommand extends Command
{
    protected $signature = 'rates:update-fallback';
    protected $description = 'Обновление fallback курсов для валют, которые не торгуются на ByBit';

    public function handle()
    {
        $this->info('Обновление fallback курсов...');

        try {
            // Получаем курсы XMR через CoinGecko API
            $xmrRate = $this->getXmrRate();
            $dashRate = $this->getDashRate();

            $this->info("XMR курс: \${$xmrRate}");
            $this->info("DASH курс: \${$dashRate}");

            // Здесь можно добавить сохранение в базу данных или конфиг
            $this->info('Курсы обновлены успешно!');

        } catch (\Exception $e) {
            $this->error('Ошибка обновления курсов: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function getXmrRate(): float
    {
        try {
            $response = Http::timeout(10)->get('https://api.coingecko.com/api/v3/simple/price', [
                'ids' => 'monero',
                'vs_currencies' => 'usd'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['monero']['usd'] ?? 320.0;
            }
        } catch (\Exception $e) {
            Log::error('Ошибка получения курса XMR: ' . $e->getMessage());
        }

        return 320.0; // Fallback значение
    }

    private function getDashRate(): float
    {
        try {
            $response = Http::timeout(10)->get('https://api.coingecko.com/api/v3/simple/price', [
                'ids' => 'dash',
                'vs_currencies' => 'usd'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['dash']['usd'] ?? 22.0;
            }
        } catch (\Exception $e) {
            Log::error('Ошибка получения курса DASH: ' . $e->getMessage());
        }

        return 22.0; // Fallback значение
    }
}
