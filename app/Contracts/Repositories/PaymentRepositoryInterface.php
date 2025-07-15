<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface PaymentRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Получить платежи с пагинацией
     */
    public function getPaginated(int $perPage = 10): LengthAwarePaginator;

    /**
     * Получить платежи за период
     */
    public function getByDateRange(\Carbon\Carbon $from, \Carbon\Carbon $to): Collection;

    /**
     * Получить статистику платежей
     */
    public function getStatistics(): array;

    /**
     * Создать платеж с историей
     */
    public function createWithHistory(array $data): \App\Models\Payment;
}
