<?php

namespace App\Contracts\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface DailyUsdtTotalRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Получить данные за период
     */
    public function getByDateRange(Carbon $from, Carbon $to): Collection;

    /**
     * Получить последние записи
     */
    public function getLatest(int $limit = 30): Collection;

    /**
     * Создать или обновить запись за дату
     */
    public function createOrUpdateForDate(Carbon $date, float $total, float $delta = 0): \App\Models\DailyUsdtTotal;

    /**
     * Получить итоги по месяцам
     */
    public function getMonthlyTotals(): array;

    /**
     * Получить статистику
     */
    public function getStatistics(): array;
}
