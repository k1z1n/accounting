<?php

namespace App\Repositories;

use App\Contracts\Repositories\PaymentRepositoryInterface;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentRepository extends BaseRepository implements PaymentRepositoryInterface
{
    public function __construct(Payment $payment)
    {
        parent::__construct($payment);
    }

    /**
     * Получить платежи с пагинацией
     */
    public function getPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Получить платежи за период
     */
    public function getByDateRange(Carbon $from, Carbon $to): Collection
    {
        return $this->model
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Получить статистику платежей
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
     * Создать платеж с историей
     */
    public function createWithHistory(array $data): Payment
    {
        $payment = $this->create($data);

        // Создаем запись в истории, если указана валюта
        if (!empty($data['currency_id']) && !empty($data['amount'])) {
            app(\App\Contracts\Repositories\HistoryRepositoryInterface::class)
                ->createForOperation(Payment::class, $payment->id, [
                    'amount' => -$data['amount'], // Отрицательная сумма для расхода
                    'currency_id' => $data['currency_id'],
                ]);
        }

        return $payment;
    }
}
