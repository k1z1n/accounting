<?php

namespace App\Console\Commands;

use App\Services\CookieManagerService;
use Illuminate\Console\Command;

class RefreshCookiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cookies:refresh-background {exchanger? : Имя обменника (obama|ural|all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Фоновое обновление куки обменников';

    /**
     * Execute the console command.
     */
    public function handle(CookieManagerService $cookieManager): int
    {
        $exchanger = $this->argument('exchanger');

        if ($exchanger && $exchanger !== 'all') {
            $this->info("Обновляю куки для {$exchanger}...");
            $cookies = $cookieManager->getFreshCookies($exchanger);

            if ($cookies) {
                $this->info("✅ Куки обновлены для {$exchanger}");
                return 0;
            } else {
                $this->error("❌ Не удалось обновить куки для {$exchanger}");
                return 1;
            }
        } else {
            $this->info("Обновляю куки для всех обменников...");
            $results = $cookieManager->refreshAllCookies();

            $success = true;
            foreach ($results as $name => $result) {
                if ($result['success']) {
                    $this->info("✅ {$name}: куки обновлены");
                } else {
                    $this->error("❌ {$name}: не удалось обновить куки - {$result['error']}");
                    $success = false;
                }
            }

            return $success ? 0 : 1;
        }
    }
}
