<?php

namespace App\Console\Commands;

use App\Models\SiteCookie;
use App\Services\ApplicationService;
use Illuminate\Console\Command;

class TestSiteCookiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:site-cookies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Тестирование работы с данными сайтов из БД';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Тестирование данных сайтов из БД ===');

        // Тест 1: Получение данных из БД
        $this->info('1. Получение данных из БД:');
        $siteCookies = SiteCookie::all();

        foreach ($siteCookies as $site) {
            $this->line("   {$site->name}: {$site->url}");
            $this->line("   Cookies: " . $site->getCookiesString());
            $this->line('');
        }

        // Тест 2: Симуляция работы ApplicationService
        $this->info('2. Симуляция работы ApplicationService:');

        $siteCookies = SiteCookie::all()->keyBy('name');

        $exchangers = [];

        if ($siteCookies->has('OBAMA')) {
            $obama = $siteCookies['OBAMA'];
            $exchangers['obama'] = [
                'url' => $obama->url . '&page_num=1',
                'cookies' => $obama->getCookiesString(),
            ];
        }

        if ($siteCookies->has('URAL')) {
            $ural = $siteCookies['URAL'];
            $exchangers['ural'] = [
                'url' => $ural->url . '&page_num=1',
                'cookies' => $ural->getCookiesString(),
            ];
        }

        foreach ($exchangers as $name => $config) {
            $this->line("   {$name}:");
            $this->line("     URL: {$config['url']}");
            $this->line("     Cookies: {$config['cookies']}");
            $this->line('');
        }

        // Тест 3: Проверка метода getCookiesArray
        $this->info('3. Проверка метода getCookiesArray:');
        foreach ($siteCookies as $site) {
            $this->line("   {$site->name}:");
            $cookiesArray = $site->getCookiesArray();
            foreach ($cookiesArray as $key => $value) {
                $this->line("     {$key}: {$value}");
            }
            $this->line('');
        }

        $this->info('Тестирование завершено!');
    }
}
