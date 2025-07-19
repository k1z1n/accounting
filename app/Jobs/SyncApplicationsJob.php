<?php

namespace App\Jobs;

use App\Services\ApplicationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncApplicationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $pageNum = 1
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ApplicationService $applicationService): void
    {
        Log::info("Запуск фоновой синхронизации заявок для страницы {$this->pageNum}");

        try {
            $applicationService->syncFromExternalSources($this->pageNum);
            Log::info("Фоновая синхронизация заявок завершена для страницы {$this->pageNum}");
        } catch (\Exception $e) {
            Log::error("Ошибка фоновой синхронизации заявок для страницы {$this->pageNum}: " . $e->getMessage());
            throw $e;
        }
    }
}








