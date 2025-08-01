<?php

namespace App\Repositories;

use App\Contracts\Repositories\HistoryRepositoryInterface;
use App\Models\History;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class HistoryRepository extends BaseRepository implements HistoryRepositoryInterface
{
    public function __construct(History $history)
    {
        parent::__construct($history);
    }

    /**
     * Получить историю с валютами
     */
    public function getAllWithCurrencies(): Collection
    {
        return $this->model->with('currency')->orderBy('created_at', 'desc')->get();
    }

    /**
     * Получить баланс по валютам
     */
    public function getCurrencyBalances(): array
    {
        return $this->model
            ->whereNotNull('currency_id')
            ->groupBy('currency_id')
            ->select('currency_id', DB::raw('SUM(amount) as balance'))
            ->with('currency')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->currency->code => $item->balance];
            })
            ->toArray();
    }

    /**
     * Получить историю за период
     */
    public function getByDateRange(Carbon $from, Carbon $to): Collection
    {
        return $this->model
            ->whereBetween('created_at', [$from, $to])
            ->with('currency')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Получить историю по валюте
     */
    public function getByCurrency(int $currencyId): Collection
    {
        return $this->model
            ->where('currency_id', $currencyId)
            ->with('currency')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Получить общий объем операций по валютам
     */
    public function getCurrencyVolumes(int $limit = 10): array
    {
        return $this->model
            ->select('currency_id', DB::raw('SUM(ABS(amount)) as total_volume'))
            ->whereNotNull('currency_id')
            ->groupBy('currency_id')
            ->orderByDesc('total_volume')
            ->limit($limit)
            ->with('currency')
            ->get()
            ->map(function ($item) {
                return [
                    'currency' => $item->currency->code ?? 'Unknown',
                    'volume' => $item->total_volume,
                ];
            })
            ->toArray();
    }

    /**
     * Создать запись истории для операции
     */
    public function createForOperation(string $operationType, int $operationId, array $data): void
    {
        $this->create([
            'sourceable_type' => $operationType,
            'sourceable_id' => $operationId,
            'amount' => $data['amount'],
            'currency_id' => $data['currency_id'],
            'created_at' => now(),
        ]);
    }

    /**
     * Найти записи истории для конкретной операции
     */
    public function findByOperation(string $operationType, int $operationId): Collection
    {
        return $this->model
            ->where('sourceable_type', $operationType)
            ->where('sourceable_id', $operationId)
            ->get();
    }

    /**
     * Найти запись истории для конкретной операции и валюты
     */
    public function findByOperationAndCurrency(string $operationType, int $operationId, int $currencyId): ?History
    {
        return $this->model
            ->where('sourceable_type', $operationType)
            ->where('sourceable_id', $operationId)
            ->where('currency_id', $currencyId)
            ->first();
    }

    /**
     * Обновить или создать запись истории для операции
     */
    public function updateOrCreateForOperation(string $operationType, int $operationId, array $data): void
    {
        $existingRecord = $this->findByOperationAndCurrency(
            $operationType,
            $operationId,
            $data['currency_id']
        );

        if ($existingRecord) {
            // Обновляем существующую запись
            $existingRecord->update([
                'amount' => $data['amount'],
                'updated_at' => now(),
            ]);
        } else {
            // Создаем новую запись
            $this->createForOperation($operationType, $operationId, $data);
        }
    }

    /**
     * Получить последние операции
     */
    public function getLatest(int $limit = 10): Collection
    {
        return $this->model
            ->with('currency')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Получить сумму по валюте
     */
    public function getSumByCurrency(int $currencyId): float
    {
        return $this->model
            ->where('currency_id', $currencyId)
            ->sum('amount');
    }
}
