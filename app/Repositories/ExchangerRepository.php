<?php

namespace App\Repositories;

use App\Contracts\Repositories\ExchangerRepositoryInterface;
use App\Models\Exchanger;
use Illuminate\Database\Eloquent\Collection;

class ExchangerRepository extends BaseRepository implements ExchangerRepositoryInterface
{
    public function __construct(Exchanger $exchanger)
    {
        parent::__construct($exchanger);
    }

    /**
     * Найти обменник по названию
     */
    public function findByTitle(string $title): ?Exchanger
    {
        return $this->model->where('title', $title)->first();
    }

    /**
     * Получить активные обменники
     */
    public function getActive(): Collection
    {
        return $this->model->where('is_active', true)->orderBy('title')->get();
    }

    /**
     * Получить обменники с сортировкой по названию
     */
    public function getAllOrdered(): Collection
    {
        return $this->model->orderBy('title')->get();
    }

    /**
     * Активировать/деактивировать обменник
     */
    public function toggleActive(int $exchangerId): bool
    {
        $exchanger = $this->findOrFail($exchangerId);
        $exchanger->is_active = !$exchanger->is_active;
        return $exchanger->save();
    }

    /**
     * Обновить настройки обменника
     */
    public function updateSettings(int $exchangerId, array $settings): Exchanger
    {
        $exchanger = $this->findOrFail($exchangerId);
        $exchanger->update($settings);
        return $exchanger->fresh();
    }
}
