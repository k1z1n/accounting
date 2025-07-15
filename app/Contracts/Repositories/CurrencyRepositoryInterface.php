<?php

namespace App\Contracts\Repositories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Collection;

interface CurrencyRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Найти валюту по коду
     */
    public function findByCode(string $code): ?Currency;

    /**
     * Создать валюту или найти существующую
     */
    public function firstOrCreate(string $code, string $name = null): Currency;

    /**
     * Получить валюты с иконками
     */
    public function getAllWithIcons(): Collection;

    /**
     * Получить валюты по списку кодов
     */
    public function findByCodes(array $codes): Collection;

    /**
     * Получить популярные валюты (по количеству операций)
     */
    public function getPopularCurrencies(int $limit = 10): Collection;

    /**
     * Обновить цвет валюты
     */
    public function updateColor(int $currencyId, string $color): bool;

    /**
     * Получить статистику использования валют
     */
    public function getUsageStatistics(): array;

    /**
     * Поиск валют по названию или коду
     */
    public function search(string $query): Collection;
}
