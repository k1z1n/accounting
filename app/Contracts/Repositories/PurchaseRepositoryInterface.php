<?php

namespace App\Contracts\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface PurchaseRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Получить покупки с пагинацией
     */
    public function getPaginated(int $perPage = 10): LengthAwarePaginator;

    /**
     * Получить покупки за период
     */
    public function getByDateRange(Carbon $from, Carbon $to): Collection;

    /**
     * Получить статистику покупок
     */
    public function getStatistics(): array;

    /**
     * Создать покупку с историей
     */
    public function createWithHistory(array $data): \App\Models\Purchase;
}
