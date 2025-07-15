<?php

namespace App\Services;

use App\Contracts\Repositories\LoginLogRepositoryInterface;
use App\Contracts\Repositories\UpdateLogRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\AuditServiceInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditService implements AuditServiceInterface
{
    public function __construct(
        private UpdateLogRepositoryInterface $updateLogRepository,
        private LoginLogRepositoryInterface $loginLogRepository,
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Получить логи изменений с пагинацией
     */
    public function getUpdateLogs(int $perPage = 10): LengthAwarePaginator
    {
        return $this->updateLogRepository->getPaginated($perPage);
    }

    /**
     * Получить логи входов с пагинацией
     */
    public function getLoginLogs(int $perPage = 10): LengthAwarePaginator
    {
        return $this->loginLogRepository->getPaginated($perPage);
    }

    /**
     * Получить активность пользователей
     */
    public function getUserActivity(int $userId = null): Collection
    {
        if ($userId) {
            return $this->updateLogRepository->getByUser($userId);
        }

        return $this->updateLogRepository->all();
    }

    /**
     * Получить статистику аудита
     */
    public function getAuditStatistics(): array
    {
        $loginStats = $this->loginLogRepository->getLoginStatistics();

        return [
            'login_statistics' => $loginStats,
            'update_logs' => [
                'total_updates' => $this->updateLogRepository->count(),
                'today_updates' => $this->updateLogRepository->findWhere([
                    ['created_at', '>=', Carbon::today()]
                ])->count(),
                'this_week_updates' => $this->updateLogRepository->findWhere([
                    ['created_at', '>=', Carbon::now()->startOfWeek()]
                ])->count(),
            ],
            'active_users' => [
                'total_users' => $this->userRepository->count(),
                'logged_in_today' => $loginStats['unique_users_today'],
                'most_active' => $loginStats['most_active_users']->take(5),
            ],
        ];
    }

    /**
     * Логировать изменение
     */
    public function logUpdate(int $userId, string $sourceableType, int $sourceableId, string $update): void
    {
        $this->updateLogRepository->logUpdate($userId, $sourceableType, $sourceableId, $update);
    }

    /**
     * Логировать вход в систему
     */
    public function logLogin(int $userId, string $ip, string $userAgent): void
    {
        $this->loginLogRepository->logLogin($userId, $ip, $userAgent);
    }

    /**
     * Получить отчет за период
     */
    public function getPeriodReport(Carbon $from, Carbon $to): array
    {
        $updateLogs = $this->updateLogRepository->getByDateRange($from, $to);
        $loginLogs = $this->loginLogRepository->getByDateRange($from, $to);

        return [
            'period' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ],
            'summary' => [
                'total_updates' => $updateLogs->count(),
                'total_logins' => $loginLogs->count(),
                'unique_users' => $loginLogs->pluck('user_id')->unique()->count(),
                'unique_ips' => $loginLogs->pluck('ip')->unique()->count(),
            ],
            'update_logs' => $updateLogs->groupBy('sourceable_type')->map(function ($logs, $type) {
                return [
                    'type' => $type,
                    'count' => $logs->count(),
                    'users' => $logs->pluck('user_id')->unique()->count(),
                ];
            })->values(),
            'daily_activity' => $this->getDailyActivity($updateLogs, $loginLogs, $from, $to),
            'most_active_users' => $this->getMostActiveUsers($updateLogs, $loginLogs),
        ];
    }

    /**
     * Получить ежедневную активность
     */
    private function getDailyActivity(Collection $updateLogs, Collection $loginLogs, Carbon $from, Carbon $to): array
    {
        $activity = [];

        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');

            $activity[$dateStr] = [
                'date' => $dateStr,
                'updates' => $updateLogs->filter(function ($log) use ($date) {
                    return $log->created_at->format('Y-m-d') === $date->format('Y-m-d');
                })->count(),
                'logins' => $loginLogs->filter(function ($log) use ($date) {
                    return $log->created_at->format('Y-m-d') === $date->format('Y-m-d');
                })->count(),
            ];
        }

        return array_values($activity);
    }

    /**
     * Получить наиболее активных пользователей
     */
    private function getMostActiveUsers(Collection $updateLogs, Collection $loginLogs): array
    {
        $userActivity = [];

        // Подсчет активности по изменениям
        foreach ($updateLogs->groupBy('user_id') as $userId => $logs) {
            $userActivity[$userId]['updates'] = $logs->count();
        }

        // Подсчет активности по входам
        foreach ($loginLogs->groupBy('user_id') as $userId => $logs) {
            $userActivity[$userId]['logins'] = $logs->count();
        }

        // Сортировка по общей активности
        uasort($userActivity, function ($a, $b) {
            $totalA = ($a['updates'] ?? 0) + ($a['logins'] ?? 0);
            $totalB = ($b['updates'] ?? 0) + ($b['logins'] ?? 0);
            return $totalB <=> $totalA;
        });

        return array_slice($userActivity, 0, 10, true);
    }
}
