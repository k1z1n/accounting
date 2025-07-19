<?php

namespace App\Console\Commands;

use App\Services\CookieManagerService;
use Illuminate\Console\Command;

class ManageCookiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cookies:manage {action=status : Действие (status|refresh|test)} {exchanger? : Имя обменника (obama|ural)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Управление куки обменников';

    /**
     * Execute the console command.
     */
    public function handle(CookieManagerService $cookieManager)
    {
        $action = $this->argument('action');
        $exchanger = $this->argument('exchanger');

        switch ($action) {
            case 'status':
                $this->showStatus($cookieManager, $exchanger);
                break;
            case 'refresh':
                $this->refreshCookies($cookieManager, $exchanger);
                break;
            case 'test':
                $this->testCookies($cookieManager, $exchanger);
                break;
            default:
                $this->error("Неизвестное действие: {$action}");
                return 1;
        }

        return 0;
    }

    /**
     * Показать статус куки
     */
    private function showStatus(CookieManagerService $cookieManager, ?string $exchanger): void
    {
        $this->info('Статус куки обменников:');
        $this->newLine();

        $status = $cookieManager->getCookiesStatus();

        foreach ($status as $name => $info) {
            if ($exchanger && $name !== $exchanger) {
                continue;
            }

            $this->line("📊 {$name}:");
            $this->line("   Учетные данные: " . ($info['has_credentials'] ? '✅' : '❌'));
            $this->line("   PIN код: " . ($info['has_pin'] ? '✅' : '❌'));
            $this->line("   Текущие куки валидны: " . ($info['current_cookies_valid'] ? '✅' : '❌'));
            $this->line("   Автообновление: " . ($info['can_auto_refresh'] ? '✅' : '❌'));
            $this->newLine();
        }
    }

    /**
     * Обновить куки
     */
    private function refreshCookies(CookieManagerService $cookieManager, ?string $exchanger): void
    {
        if ($exchanger) {
            $this->info("Обновляю куки для {$exchanger}...");
            $cookies = $cookieManager->getFreshCookies($exchanger);

            if ($cookies) {
                $this->info("✅ Новые куки получены для {$exchanger}");
                $this->line("Куки: " . substr($cookies, 0, 100) . "...");
            } else {
                $this->error("❌ Не удалось получить куки для {$exchanger}");
            }
        } else {
            $this->info("Обновляю куки для всех обменников...");
            $results = $cookieManager->refreshAllCookies();

            foreach ($results as $name => $result) {
                if ($result['success']) {
                    $this->info("✅ {$name}: куки обновлены");
                } else {
                    $this->error("❌ {$name}: не удалось обновить куки");
                }
            }
        }
    }

    /**
     * Протестировать куки
     */
    private function testCookies(CookieManagerService $cookieManager, ?string $exchanger): void
    {
        $this->info('Тестирование куки...');

        $status = $cookieManager->getCookiesStatus();

        foreach ($status as $name => $info) {
            if ($exchanger && $name !== $exchanger) {
                continue;
            }

            $this->line("🧪 Тестируем {$name}...");

            if (!$info['has_credentials']) {
                $this->error("   ❌ Нет учетных данных");
                continue;
            }

            if ($info['current_cookies_valid']) {
                $this->info("   ✅ Текущие куки работают");
            } else {
                $this->warn("   ⚠️ Текущие куки не работают, пытаемся обновить...");

                $newCookies = $cookieManager->getFreshCookies($name);
                if ($newCookies) {
                    $this->info("   ✅ Новые куки получены");
                } else {
                    $this->error("   ❌ Не удалось получить новые куки");
                }
            }
        }
    }
}












