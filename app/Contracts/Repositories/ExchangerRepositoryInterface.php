<?php

namespace App\Contracts\Repositories;

use App\Models\Exchanger;
use Illuminate\Database\Eloquent\Collection;

interface ExchangerRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Найти обменник по названию
     */
    public function findByTitle(string $title): ?Exchanger;

    /**
     * Получить активные обменники
     */
    public function getActive(): Collection;

    /**
     * Получить обменники с сортировкой по названию
     */
    public function getAllOrdered(): Collection;

    /**
     * Активировать/деактивировать обменник
     */
    public function toggleActive(int $exchangerId): bool;

    /**
     * Обновить настройки обменника
     */
    public function updateSettings(int $exchangerId, array $settings): Exchanger;
}
