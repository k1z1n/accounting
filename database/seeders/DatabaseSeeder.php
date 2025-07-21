<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Exchanger;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Создаем тестовых пользователей
        User::factory(5)->create();

        $exchangers = [
            'Obama Heleket',
            'Obama Rapira',
            'Ural Heleket',
            'Ural Rapira',
        ];

        $currencies = [
            'USDT' => 'юсдт', 'RUB' => 'юсдт', 'BTC' => 'юсдт', 'LTC' => 'юсдт', 'TRX' => 'юсдт'
        ];

        foreach ($currencies as $code => $name) {
            Currency::create([
                'code' => $code,
                'name' => $name
            ]);
        }

        foreach ($exchangers as $exchanger) {
            Exchanger::create([
                'title' => $exchanger,
            ]);
        }

        // Запускаем seeder для ролей пользователей
        $this->call([
            UserRolesSeeder::class,
        ]);

        // Запускаем сидеры для тестовых данных
        $this->call([
            PaymentSeeder::class,
            SaleCryptSeeder::class,
            PurchaseSeeder::class,
            TransferSeeder::class,
        ]);
    }
}
