<?php

namespace App\Repositories;

use App\Contracts\Repositories\LoginLogRepositoryInterface;
use App\Models\LoginLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LoginLogRepository extends BaseRepository implements LoginLogRepositoryInterface
{
    public function __construct(LoginLog $loginLog)
    {
        parent::__construct($loginLog);
    }

    /**
     * Получить логи входов с пагинацией
     */
    public function getPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->with('user')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Получить логи за период
     */
    public function getByDateRange(Carbon $from, Carbon $to): Collection
    {
        return $this->model->with('user')
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Получить логи по пользователю
     */
    public function getByUser(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Получить логи по IP
     */
    public function getByIp(string $ip): Collection
    {
        return $this->model->with('user')
            ->where('ip', $ip)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Получить статистику входов
     */
    public function getLoginStatistics(): array
    {
        return [
            'total_logins' => $this->model->count(),
            'today_logins' => $this->model->whereDate('created_at', today())->count(),
            'unique_users_today' => $this->model
                ->whereDate('created_at', today())
                ->distinct('user_id')
                ->count('user_id'),
            'most_active_users' => $this->model
                ->select('user_id', DB::raw('COUNT(*) as login_count'))
                ->with('user')
                ->groupBy('user_id')
                ->orderByDesc('login_count')
                ->limit(10)
                ->get(),
            'login_by_hour' => $this->model
                ->whereDate('created_at', today())
                ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
                ->groupBy('hour')
                ->orderBy('hour')
                ->get()
                ->pluck('count', 'hour')
                ->toArray(),
        ];
    }

    /**
     * Создать лог входа
     */
    public function logLogin(int $userId, string $ip, string $userAgent): void
    {
        $this->create([
            'user_id' => $userId,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'created_at' => now(),
        ]);
    }
}
