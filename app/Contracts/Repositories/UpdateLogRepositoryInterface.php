<?php

namespace App\Contracts\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface UpdateLogRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Получить логи с пагинацией
     */
    public function getPaginated(int $perPage = 10): LengthAwarePaginator;

    /**
     * Получить логи за период
     */
    public function getByDateRange(Carbon $from, Carbon $to): Collection;

    /**
     * Получить логи по пользователю
     */
    public function getByUser(int $userId): Collection;

    /**
     * Получить логи по сущности
     */
    public function getBySourceable(string $sourceableType, int $sourceableId): Collection;

    /**
     * Создать лог изменения
     */
    public function logUpdate(int $userId, string $sourceableType, int $sourceableId, string $update): void;
}
