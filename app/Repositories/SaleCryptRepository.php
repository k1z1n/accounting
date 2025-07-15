<?php

namespace App\Repositories;

use App\Contracts\Repositories\SaleCryptRepositoryInterface;
use App\Models\SaleCrypt;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SaleCryptRepository extends BaseRepository implements SaleCryptRepositoryInterface
{
    public function __construct(SaleCrypt $saleCrypt)
    {
        parent::__construct($saleCrypt);
    }

    /**
     * Получить продажи с пагинацией
     */
    public function getPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Получить продажи за период
     */
    public function getByDateRange(Carbon $from, Carbon $to): Collection
    {
        return $this->model
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Получить статистику продаж
     */
    public function getStatistics(): array
    {
        return [
            'total_count' => $this->model->count(),
            'total_amount' => $this->model->sum('amount'),
            'today_count' => $this->model->whereDate('created_at', today())->count(),
            'this_month_count' => $this->model->whereMonth('created_at', now()->month)->count(),
        ];
    }

    /**
     * Создать продажу с историей
     */
    public function createWithHistory(array $data): SaleCrypt
    {
        $saleCrypt = $this->create($data);

        // Создаем запись в истории, если указана валюта
        if (!empty($data['currency_id']) && !empty($data['amount'])) {
            app(\App\Contracts\Repositories\HistoryRepositoryInterface::class)
                ->createForOperation(SaleCrypt::class, $saleCrypt->id, [
                    'amount' => -$data['amount'], // Отрицательная сумма для продажи
                    'currency_id' => $data['currency_id'],
                ]);
        }

        return $saleCrypt;
    }
}
