<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SyncApplicationsJob;
use App\Jobs\SyncApplicationsRangeJob;
use Illuminate\Support\Facades\Log;

class SyncApplicationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'applications:sync {page? : Номер страницы для синхронизации} {--range : Синхронизировать диапазон страниц} {--start=1 : Начальная страница диапазона} {--end=5 : Конечная страница диапазона}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Синхронизирует заявки с внешних источников';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $page = $this->argument('page');
        $isRange = $this->option('range');
        $startPage = (int)$this->option('start');
        $endPage = (int)$this->option('end');

        if ($isRange) {
            $this->info("Запуск синхронизации диапазона страниц {$startPage}-{$endPage}...");

            try {
                SyncApplicationsRangeJob::dispatch($startPage, $endPage);
                $this->info("Синхронизация диапазона {$startPage}-{$endPage} запущена в фоновом режиме");
                Log::info("SyncApplicationsCommand: синхронизация диапазона {$startPage}-{$endPage} запущена");
            } catch (\Exception $e) {
                $this->error("Ошибка запуска синхронизации диапазона: " . $e->getMessage());
                Log::error("SyncApplicationsCommand: ошибка запуска синхронизации диапазона", [
                    'error' => $e->getMessage(),
                    'start_page' => $startPage,
                    'end_page' => $endPage
                ]);
                return 1;
            }
        } else {
            $page = $page ?: 1;
            $this->info("Запуск синхронизации страницы {$page}...");

            try {
                SyncApplicationsJob::dispatch($page);
                $this->info("Синхронизация страницы {$page} запущена в фоновом режиме");
                Log::info("SyncApplicationsCommand: синхронизация страницы {$page} запущена");
            } catch (\Exception $e) {
                $this->error("Ошибка запуска синхронизации: " . $e->getMessage());
                Log::error("SyncApplicationsCommand: ошибка запуска синхронизации", [
                    'error' => $e->getMessage(),
                    'page' => $page
                ]);
                return 1;
            }
        }

        return 0;
    }
}








