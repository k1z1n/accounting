<?php

namespace App\Contracts\Repositories;

use App\Models\Application;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ApplicationRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Получить заявки с отношениями
     */
    public function getAllWithRelations(): Collection;

    /**
     * Получить пагинированные заявки с отношениями
     */
    public function getPaginatedWithRelations(int $perPage = 20): LengthAwarePaginator;

    /**
     * Найти заявки по статусу
     */
    public function findByStatus(array $statuses): Collection;

    /**
     * Найти заявки по обменнику
     */
    public function findByExchanger(string $exchanger): Collection;

    /**
     * Получить заявки за период
     */
    public function getByDateRange(\Carbon\Carbon $from, \Carbon\Carbon $to): Collection;

    /**
     * Синхронизация заявок (upsert)
     */
    public function syncApplications(array $applications): void;

    /**
     * Получить статистику по статусам
     */
    public function getStatusStatistics(): array;

    /**
     * Получить статистику по обменникам
     */
    public function getExchangerStatistics(): array;

    /**
     * Получить заявки по ID обменника и ID заявки
     */
    public function findByExchangerAndAppId(string $exchanger, string $appId): ?Application;

    /**
     * Получить динамику заявок по дням
     */
    public function getDailyStatistics(int $days = 30): array;

    /**
     * Обновить связанные валюты заявки
     */
    public function updateCurrencies(int $applicationId, array $currencyData): Application;
}
