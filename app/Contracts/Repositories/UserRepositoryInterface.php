<?php

namespace App\Contracts\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Найти пользователя по логину
     */
    public function findByLogin(string $login): ?User;

    /**
     * Получить пользователей по роли
     */
    public function findByRole(string $role): Collection;

    /**
     * Получить заблокированных пользователей
     */
    public function getBlocked(): Collection;

    /**
     * Получить активных пользователей
     */
    public function getActive(): Collection;

    /**
     * Изменить статус блокировки
     */
    public function toggleBlocked(int $userId): bool;

    /**
     * Изменить роль пользователя
     */
    public function changeRole(int $userId, string $role): bool;

    /**
     * Получить статистику по ролям
     */
    public function getRoleStatistics(): array;

    /**
     * Получить пользователей, активных за период
     */
    public function getActiveInPeriod(\Carbon\Carbon $from, \Carbon\Carbon $to): Collection;
}
