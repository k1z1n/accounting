<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Exchanger;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $exchangers = Exchanger::all();
        $currencies = Currency::all();

        $statuses = ['выполненная заявка', 'оплаченная заявка', 'возврат'];
        $exchangerNames = ['obama', 'ural'];

        for ($i = 1; $i <= 40; $i++) {
            $user = $users->random();
            $exchanger = $exchangers->random();
            $receivedCurrency = $currencies->random();
            $saleCurrency = $currencies->random();
            $status = $statuses[array_rand($statuses)];
            $exchangerName = $exchangerNames[array_rand($exchangerNames)];

            // Генерируем случайные суммы
            $receivedAmount = rand(1, 1000) + (rand(0, 99) / 100);
            $saleAmount = rand(1, 1000) + (rand(0, 99) / 100);

            Purchase::create([
                'exchanger_id' => $exchanger->id,
                'received_amount' => $receivedAmount,
                'received_currency_id' => $receivedCurrency->id,
                'sale_amount' => $saleAmount,
                'sale_currency_id' => $saleCurrency->id,
                'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
                'updated_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
            ]);
        }
    }
}
