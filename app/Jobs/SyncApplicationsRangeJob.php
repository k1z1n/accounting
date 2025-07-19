<?php

namespace App\Jobs;

use App\Services\ApplicationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncApplicationsRangeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $startPage = 1,
        private int $endPage = 1
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ApplicationService $applicationService): void
    {
        Log::info("Запуск фоновой синхронизации заявок для страниц {$this->startPage}-{$this->endPage}");

        try {
            for ($page = $this->startPage; $page <= $this->endPage; $page++) {
                $applicationService->syncFromExternalSources($page);
                Log::info("Синхронизирована страница {$page}");
            }

            Log::info("Фоновая синхронизация заявок завершена для страниц {$this->startPage}-{$this->endPage}");
        } catch (\Exception $e) {
            Log::error("Ошибка фоновой синхронизации заявок для страниц {$this->startPage}-{$this->endPage}: " . $e->getMessage());
            throw $e;
        }
    }
}












