<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SyncApplicationsRangeJob;
use Illuminate\Support\Facades\Log;

class SyncApplicationsScheduledCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'applications:sync-scheduled {--pages=3 : Количество страниц для синхронизации}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Периодическая синхронизация заявок с внешних источников';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pagesCount = (int)$this->option('pages');
        $startPage = 1;
        $endPage = $pagesCount;

        $this->info("Запуск периодической синхронизации страниц {$startPage}-{$endPage}...");

        try {
            SyncApplicationsRangeJob::dispatch($startPage, $endPage);
            $this->info("Периодическая синхронизация страниц {$startPage}-{$endPage} запущена в фоновом режиме");
            Log::info("SyncApplicationsScheduledCommand: периодическая синхронизация страниц {$startPage}-{$endPage} запущена");
        } catch (\Exception $e) {
            $this->error("Ошибка запуска периодической синхронизации: " . $e->getMessage());
            Log::error("SyncApplicationsScheduledCommand: ошибка запуска периодической синхронизации", [
                'error' => $e->getMessage(),
                'start_page' => $startPage,
                'end_page' => $endPage
            ]);
            return 1;
        }

        return 0;
    }
}
