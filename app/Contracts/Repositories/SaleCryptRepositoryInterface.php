<?php

namespace App\Contracts\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface SaleCryptRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Получить продажи с пагинацией
     */
    public function getPaginated(int $perPage = 10): LengthAwarePaginator;

    /**
     * Получить продажи за период
     */
    public function getByDateRange(Carbon $from, Carbon $to): Collection;

    /**
     * Получить статистику продаж
     */
    public function getStatistics(): array;

    /**
     * Создать продажу с историей
     */
    public function createWithHistory(array $data): \App\Models\SaleCrypt;
}
