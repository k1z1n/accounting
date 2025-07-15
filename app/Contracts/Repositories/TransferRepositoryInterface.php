<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TransferRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Получить переводы с пагинацией
     */
    public function getPaginated(int $perPage = 10): LengthAwarePaginator;

    /**
     * Получить переводы за период
     */
    public function getByDateRange(\Carbon\Carbon $from, \Carbon\Carbon $to): Collection;

    /**
     * Получить статистику переводов
     */
    public function getStatistics(): array;

    /**
     * Создать перевод с историей
     */
    public function createWithHistory(array $data): \App\Models\Transfer;
}
