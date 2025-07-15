<?php

namespace App\Contracts\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface LoginLogRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Получить логи входов с пагинацией
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
     * Получить логи по IP
     */
    public function getByIp(string $ip): Collection;

    /**
     * Получить статистику входов
     */
    public function getLoginStatistics(): array;

    /**
     * Создать лог входа
     */
    public function logLogin(int $userId, string $ip, string $userAgent): void;
}
