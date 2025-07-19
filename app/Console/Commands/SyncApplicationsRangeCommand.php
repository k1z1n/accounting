<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SyncApplicationsRangeJob;
use Illuminate\Support\Facades\Log;

class SyncApplicationsRangeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'applications:sync-range {start=1 : Начальная страница} {end=5 : Конечная страница}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Синхронизирует диапазон страниц заявок с внешних источников в фоновом режиме';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startPage = (int)$this->argument('start');
        $endPage = (int)$this->argument('end');

        $this->info("Запуск синхронизации диапазона страниц {$startPage}-{$endPage}...");

        try {
            SyncApplicationsRangeJob::dispatch($startPage, $endPage);
            $this->info("Синхронизация диапазона {$startPage}-{$endPage} запущена в фоновом режиме");
            Log::info("SyncApplicationsRangeCommand: синхронизация диапазона {$startPage}-{$endPage} запущена");
        } catch (\Exception $e) {
            $this->error("Ошибка запуска синхронизации диапазона: " . $e->getMessage());
            Log::error("SyncApplicationsRangeCommand: ошибка запуска синхронизации диапазона", [
                'error' => $e->getMessage(),
                'start_page' => $startPage,
                'end_page' => $endPage
            ]);
            return 1;
        }

        return 0;
    }
}












