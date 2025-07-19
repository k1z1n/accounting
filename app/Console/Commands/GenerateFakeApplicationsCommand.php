<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\Currency;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateFakeApplicationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'applications:generate-fake {count=20 : Количество фейковых заявок}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Генерирует фейковые заявки для тестирования';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->argument('count');

        $this->info("Генерирую {$count} фейковых заявок...");

        // Создаем валюты, если их нет
        $currencies = ['USDT', 'BTC', 'ETH', 'RUB', 'USD'];
        foreach ($currencies as $code) {
            Currency::firstOrCreate(
                ['code' => $code],
                ['name' => $code]
            );
        }

        $exchangers = ['obama', 'ural'];
        $statuses = ['выполненная заявка', 'оплаченная заявка', 'возврат'];

        for ($i = 1; $i <= $count; $i++) {
            $exchanger = $exchangers[array_rand($exchangers)];
            $status = $statuses[array_rand($statuses)];

            $application = Application::create([
                'exchanger' => $exchanger,
                'app_id' => 'APP' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'app_created_at' => Carbon::now()->subDays(rand(1, 30)),
                'status' => $status,
                'sale_text' => rand(0, 1) ? rand(100, 10000) . ' USDT' : null,
                'sell_amount' => rand(0, 1) ? rand(10, 1000) : null,
                'sell_currency_id' => rand(0, 1) ? Currency::where('code', 'BTC')->first()->id : null,
                'buy_amount' => rand(0, 1) ? rand(10, 1000) : null,
                'buy_currency_id' => rand(0, 1) ? Currency::where('code', 'ETH')->first()->id : null,
                'expense_amount' => rand(0, 1) ? rand(10, 1000) : null,
                'expense_currency_id' => rand(0, 1) ? Currency::where('code', 'USDT')->first()->id : null,
                'merchant' => rand(0, 1) ? 'Merchant' . $i : null,
                'order_id' => rand(0, 1) ? 'ORDER' . str_pad($i, 6, '0', STR_PAD_LEFT) : null,
                'user_id' => 1, // Предполагаем, что есть пользователь с ID 1
            ]);

            $this->line("Создана заявка {$i}: {$application->app_id} ({$exchanger})");
        }

        $this->info("✅ Создано {$count} фейковых заявок!");
    }
}












