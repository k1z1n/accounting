<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Exchanger;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransferSeeder extends Seeder
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
            $exchangerFrom = $exchangers->random();
            $exchangerTo = $exchangers->random();
            $amountCurrency = $currencies->random();
            $commissionCurrency = $currencies->random();
            $status = $statuses[array_rand($statuses)];
            $exchangerName = $exchangerNames[array_rand($exchangerNames)];

            // Генерируем случайные суммы
            $amount = rand(100, 10000) + (rand(0, 99) / 100);
            $commission = rand(1, 100) + (rand(0, 99) / 100);

            Transfer::create([
                'exchanger_from_id' => $exchangerFrom->id,
                'exchanger_to_id' => $exchangerTo->id,
                'amount' => $amount,
                'amount_id' => $amountCurrency->id,
                'commission' => $commission,
                'commission_id' => $commissionCurrency->id,
                'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
                'updated_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
            ]);
        }
    }
}
