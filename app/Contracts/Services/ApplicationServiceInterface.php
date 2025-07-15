<?php

namespace App\Contracts\Services;

use App\Models\Application;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ApplicationServiceInterface
{
    /**
     * Получить заявки с пагинацией
     */
    public function getPaginatedApplications(int $page = 1, int $perPage = 20): LengthAwarePaginator;

    /**
     * Синхронизировать заявки с внешними источниками
     */
    public function syncFromExternalSources(int $pageNum = 1): void;

    /**
     * Обновить заявку
     */
    public function updateApplication(int $applicationId, array $data): Application;

    /**
     * Получить заявку с подробной информацией
     */
    public function getApplicationDetails(int $applicationId): Application;

    /**
     * Создать новую заявку
     */
    public function createApplication(array $data): Application;

    /**
     * Получить статистику заявок
     */
    public function getApplicationStatistics(): array;

    /**
     * Получить заявки по фильтрам
     */
    public function getFilteredApplications(array $filters): Collection;

    /**
     * Получить данные для экспорта
     */
    public function getExportData(array $filters = []): Collection;

    /**
     * Обработать валютные данные заявки
     */
    public function processCurrencyData(Application $application, array $currencyData): void;
}
