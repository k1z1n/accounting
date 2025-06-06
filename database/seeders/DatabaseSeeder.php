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
        // User::factory(10)->create();

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

        $password = Str::random(10);

        // Создаём пользователя
        $user = User::create([
            'login'          => 'admin',
            'password'       => bcrypt($password),
            'save_password'  => $password,
            'role'           => 'admin',
            'blocked'        => 'none',
            'registered_at'  => now(),
        ]);

        // Выводим пароль в консоль (php artisan db:seed)
        echo "\n===============================\n";
        echo "Admin создан!\n";
        echo "login: admin\n";
        echo "password: $password\n";
        echo "===============================\n\n";
    }
}
