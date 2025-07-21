<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Exchanger;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $exchangers = Exchanger::all();
        $currencies = Currency::all();

        $statuses = ['выполненная заявка', 'оплаченная заявка', 'возврат'];
        $exchangerNames = ['obama', 'ural'];
        $comments = [
            'Оплата за услуги',
            'Комиссия за обмен',
            'Возврат средств',
            'Пополнение баланса',
            'Вывод средств',
            'Перевод между счетами',
            'Оплата комиссии',
            'Возврат комиссии'
        ];

        for ($i = 1; $i <= 40; $i++) {
            $user = $users->random();
            $exchanger = $exchangers->random();
            $currency = $currencies->random();
            $status = $statuses[array_rand($statuses)];
            $exchangerName = $exchangerNames[array_rand($exchangerNames)];
            $comment = $comments[array_rand($comments)];

            // Генерируем случайную сумму от 10 до 10000
            $amount = rand(10, 10000) + (rand(0, 99) / 100);

            Payment::create([
                'user_id' => $user->id,
                'exchanger_id' => $exchanger->id,
                'sell_amount' => $amount,
                'sell_currency_id' => $currency->id,
                'comment' => $comment,
                'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
                'updated_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
            ]);
        }
    }
}
