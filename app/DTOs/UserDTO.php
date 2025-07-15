<?php

namespace App\DTOs;

use App\Models\User;

class UserDTO extends BaseDTO
{
    public ?int $id = null;
    public ?string $login = null;
    public ?string $password = null;
    public ?string $save_password = null;
    public ?string $role = null;
    public ?string $blocked = null;
    public ?string $registered_at = null;

    /**
     * Валидация данных пользователя
     */
    public function validate(): bool
    {
        if (empty($this->login)) {
            return false;
        }

        if (!empty($this->role) && !in_array($this->role, [
            User::ROLE_USER,
            User::ROLE_ADMIN,
            User::ROLE_ACCOUNTANT,
            User::ROLE_STATISTICIAN
        ])) {
            return false;
        }

        if (!empty($this->blocked) && !in_array($this->blocked, ['none', 'block'])) {
            return false;
        }

        return true;
    }

    /**
     * Получить данные для создания пользователя
     */
    public function getCreateData(): array
    {
        $data = $this->getModelData();

        // Устанавливаем значения по умолчанию
        $data['role'] = $data['role'] ?? User::ROLE_USER;
        $data['blocked'] = $data['blocked'] ?? 'none';
        $data['registered_at'] = $data['registered_at'] ?? now();

        return $data;
    }

    /**
     * Получить данные для создания/обновления модели
     */
    public function getModelData(): array
    {
        return array_filter($this->toArray(), function ($value) {
            return $value !== null;
        });
    }

    /**
     * Проверить силу пароля
     */
    public function hasStrongPassword(): bool
    {
        if (empty($this->password)) {
            return false;
        }

        // Минимум 6 символов
        return strlen($this->password) >= 6;
    }
}
