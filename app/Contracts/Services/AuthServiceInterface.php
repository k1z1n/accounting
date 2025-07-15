<?php

namespace App\Contracts\Services;

use App\Models\User;
use Illuminate\Http\Request;

interface AuthServiceInterface
{
    /**
     * Аутентификация пользователя
     */
    public function authenticate(string $login, string $password, bool $remember = false): User;

    /**
     * Регистрация нового пользователя
     */
    public function register(array $userData): User;

    /**
     * Выход из системы
     */
    public function logout(Request $request): void;

    /**
     * Проверить учетные данные
     */
    public function checkCredentials(string $login, string $password): bool;

    /**
     * Получить текущего пользователя
     */
    public function getCurrentUser(): ?User;

    /**
     * Проверить, авторизован ли пользователь
     */
    public function isAuthenticated(): bool;

    /**
     * Логирование входа
     */
    public function logLogin(User $user, Request $request): void;
}
