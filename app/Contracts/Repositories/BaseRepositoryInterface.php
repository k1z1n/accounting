<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    /**
     * Найти модель по ID
     */
    public function find(int $id): ?Model;

    /**
     * Найти модель по ID или выбросить исключение
     */
    public function findOrFail(int $id): Model;

    /**
     * Получить все записи
     */
    public function all(): Collection;

    /**
     * Создать новую запись
     */
    public function create(array $data): Model;

    /**
     * Обновить запись
     */
    public function update(int $id, array $data): Model;

    /**
     * Удалить запись
     */
    public function delete(int $id): bool;

    /**
     * Получить пагинированные записи
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Найти записи по условию
     */
    public function findWhere(array $criteria): Collection;

    /**
     * Найти первую запись по условию
     */
    public function findWhereFirst(array $criteria): ?Model;

    /**
     * Подсчет записей по условию
     */
    public function count(array $criteria = []): int;

    /**
     * Проверить существование записи
     */
    public function exists(array $criteria): bool;
}
