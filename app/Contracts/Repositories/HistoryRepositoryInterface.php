<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;

interface HistoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Получить историю с валютами
     */
    public function getAllWithCurrencies(): Collection;

    /**
     * Получить баланс по валютам
     */
    public function getCurrencyBalances(): array;

    /**
     * Получить историю за период
     */
    public function getByDateRange(\Carbon\Carbon $from, \Carbon\Carbon $to): Collection;

    /**
     * Получить историю по валюте
     */
    public function getByCurrency(int $currencyId): Collection;

    /**
     * Получить общий объем операций по валютам
     */
    public function getCurrencyVolumes(int $limit = 10): array;

    /**
     * Создать запись истории для операции
     */
    public function createForOperation(string $operationType, int $operationId, array $data): void;

    /**
     * Найти записи истории для конкретной операции
     */
    public function findByOperation(string $operationType, int $operationId): Collection;

    /**
     * Найти запись истории для конкретной операции и валюты
     */
    public function findByOperationAndCurrency(string $operationType, int $operationId, int $currencyId): ?\App\Models\History;

    /**
     * Обновить или создать запись истории для операции
     */
    public function updateOrCreateForOperation(string $operationType, int $operationId, array $data): void;

    /**
     * Получить последние операции
     */
    public function getLatest(int $limit = 10): Collection;

    /**
     * Получить сумму по валюте
     */
    public function getSumByCurrency(int $currencyId): float;
}
