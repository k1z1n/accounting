<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $user)
    {
        parent::__construct($user);
    }

    /**
     * Найти пользователя по логину
     */
    public function findByLogin(string $login): ?User
    {
        return $this->model->where('login', $login)->first();
    }

    /**
     * Получить пользователей по роли
     */
    public function findByRole(string $role): Collection
    {
        return $this->model->where('role', $role)->get();
    }

    /**
     * Получить заблокированных пользователей
     */
    public function getBlocked(): Collection
    {
        return $this->model->where('blocked', 'block')->get();
    }

    /**
     * Получить активных пользователей
     */
    public function getActive(): Collection
    {
        return $this->model->where('blocked', 'none')->get();
    }

    /**
     * Изменить статус блокировки
     */
    public function toggleBlocked(int $userId): bool
    {
        $user = $this->findOrFail($userId);
        $newStatus = $user->blocked === 'block' ? 'none' : 'block';

        return $user->update(['blocked' => $newStatus]);
    }

    /**
     * Изменить роль пользователя
     */
    public function changeRole(int $userId, string $role): bool
    {
        $user = $this->findOrFail($userId);
        return $user->update(['role' => $role]);
    }

    /**
     * Получить статистику по ролям
     */
    public function getRoleStatistics(): array
    {
        return $this->model
            ->select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->get()
            ->pluck('total', 'role')
            ->toArray();
    }

    /**
     * Получить пользователей, активных за период
     */
    public function getActiveInPeriod(Carbon $from, Carbon $to): Collection
    {
        return $this->model
            ->whereBetween('updated_at', [$from, $to])
            ->get();
    }

    /**
     * Создать пользователя с хешированием пароля
     */
    public function createUser(array $data): User
    {
        if (isset($data['password'])) {
            $data['save_password'] = $data['password'];
            $data['password'] = bcrypt($data['password']);
        }

        $data['registered_at'] = $data['registered_at'] ?? now();
        $data['blocked'] = $data['blocked'] ?? 'none';

        return $this->create($data);
    }

    /**
     * Обновить последнюю активность пользователя
     */
    public function updateLastActivity(int $userId): bool
    {
        return $this->model->where('id', $userId)->update(['updated_at' => now()]);
    }
}
