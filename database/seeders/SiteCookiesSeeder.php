<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiteCookiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Очищаем таблицу
        DB::table('site_cookies')->truncate();

        // Данные для OBAMA
        DB::table('site_cookies')->insert([
            'name' => 'OBAMA',
            'url' => 'https://obama.ru/wp-admin/admin.php?page=pn_bids',
            'phpsessid' => '04e030dc5bdef9d46aab5c6f7ba1b3a5',
            'premium_session_id' => 'k9RDxkEM9bg6ngS5FE4ReTtR3iEB9lqkiH2C73Laiz09q8lLvnEvfTrig7mKcyhf',
            'wordpress_logged_title' => 'wordpress_logged_in_000f37c7c9e29bc682c1113c4ab6ebfa',
            'wordpress_logged_value' => 'rafael%7C1753857439%7CJ4E3wCJ5R8FQrNd8ipMipp8mljkfT5DbNlB9Z4NdgMt%7C2136cde34930a84430192bea53be08cfac3d68dca1f3de4f7042b4ce9c75cb42',
            'wordpress_sec_title' => 'wordpress_sec_000f37c7c9e29bc682c1113c4ab6ebfa',
            'wordpress_sec_value' => 'rafael%7C1753857439%7CJ4E3wCJ5R8FQrNd8ipMipp8mljkfT5DbNlB9Z4NdgMt%7C909a6377bbde2440dde808b25b1390f0675fa1cdef6801aa4785a39f51d09aeb',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Данные для URAL
        DB::table('site_cookies')->insert([
            'name' => 'URAL',
            'url' => 'https://ural-obmen.ru/wp-admin/admin.php?page=pn_bids',
            'phpsessid' => '87cb985d4d76207795a203cef91f4e48',
            'premium_session_id' => 'AItVl0xJhe0glnYoNUwDeY7IXClMlNyROcWU9Gmi5NwF58asO7v5IfgaItUz5KgL',
            'wordpress_logged_title' => 'wordpress_logged_in_939aa296cba7e000661edfeeafb230c8',
            'wordpress_logged_value' => 'k1z1n%7C1753374724%7CCciBUzx9VYBJFM2dLta7ptvoMryDSgXn1tXEoSl9eXx%7Ca43679da83782c54e48cc23ecaf04145e6afd29b4c9ad6a977b17fbd32b1d915',
            'wordpress_sec_title' => 'wordpress_sec_939aa296cba7e000661edfeeafb230c8',
            'wordpress_sec_value' => 'k1z1n%7C1753374724%7CCciBUzx9VYBJFM2dLta7ptvoMryDSgXn1tXEoSl9eXx%7Cb6476b0403ee35ce8a3a20c3949c4df6fdb4ff2d3f865584a1f77e8e47200f1d',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
