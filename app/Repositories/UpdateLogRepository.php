<?php

namespace App\Repositories;

use App\Contracts\Repositories\UpdateLogRepositoryInterface;
use App\Models\UpdateLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UpdateLogRepository extends BaseRepository implements UpdateLogRepositoryInterface
{
    public function __construct(UpdateLog $updateLog)
    {
        parent::__construct($updateLog);
    }

    /**
     * Получить логи с пагинацией
     */
    public function getPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->with(['user', 'sourceable'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Получить логи за период
     */
    public function getByDateRange(Carbon $from, Carbon $to): Collection
    {
        return $this->model->with(['user', 'sourceable'])
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Получить логи по пользователю
     */
    public function getByUser(int $userId): Collection
    {
        return $this->model->with(['sourceable'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Получить логи по сущности
     */
    public function getBySourceable(string $sourceableType, int $sourceableId): Collection
    {
        return $this->model->with(['user'])
            ->where('sourceable_type', $sourceableType)
            ->where('sourceable_id', $sourceableId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Создать лог изменения
     */
    public function logUpdate(int $userId, string $sourceableType, int $sourceableId, string $update): void
    {
        $this->create([
            'user_id' => $userId,
            'sourceable_type' => $sourceableType,
            'sourceable_id' => $sourceableId,
            'update' => $update,
        ]);
    }
}
