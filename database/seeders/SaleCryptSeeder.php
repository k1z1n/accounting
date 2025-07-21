<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Exchanger;
use App\Models\SaleCrypt;
use App\Models\User;
use Illuminate\Database\Seeder;

class SaleCryptSeeder extends Seeder
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
            $sellCurrency = $currencies->random();
            $buyCurrency = $currencies->random();
            $status = $statuses[array_rand($statuses)];
            $exchangerName = $exchangerNames[array_rand($exchangerNames)];

            // Генерируем случайные суммы
            $sellAmount = rand(1, 1000) + (rand(0, 99) / 100);
            $buyAmount = rand(1, 1000) + (rand(0, 99) / 100);

            SaleCrypt::create([
                'exchanger_id' => $exchanger->id,
                'sale_amount' => $sellAmount,
                'sale_currency_id' => $sellCurrency->id,
                'fixed_amount' => $buyAmount,
                'fixed_currency_id' => $buyCurrency->id,
                'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
                'updated_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
            ]);
        }
    }
}
