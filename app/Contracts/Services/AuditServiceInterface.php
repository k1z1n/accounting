<?php

namespace App\Contracts\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface AuditServiceInterface
{
    /**
     * Получить логи изменений с пагинацией
     */
    public function getUpdateLogs(int $perPage = 10): LengthAwarePaginator;

    /**
     * Получить логи входов с пагинацией
     */
    public function getLoginLogs(int $perPage = 10): LengthAwarePaginator;

    /**
     * Получить активность пользователей
     */
    public function getUserActivity(int $userId = null): Collection;

    /**
     * Получить статистику аудита
     */
    public function getAuditStatistics(): array;

    /**
     * Логировать изменение
     */
    public function logUpdate(int $userId, string $sourceableType, int $sourceableId, string $update): void;

    /**
     * Логировать вход в систему
     */
    public function logLogin(int $userId, string $ip, string $userAgent): void;

    /**
     * Получить отчет за период
     */
    public function getPeriodReport(Carbon $from, Carbon $to): array;
}
