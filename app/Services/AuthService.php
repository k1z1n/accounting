<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\AuthServiceInterface;
use App\DTOs\UserDTO;
use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Аутентификация пользователя
     */
    public function authenticate(string $login, string $password, bool $remember = false): User
    {
        if (!$this->checkCredentials($login, $password)) {
            throw new AuthenticationException('Неверные учётные данные');
        }

        $user = $this->userRepository->findByLogin($login);

        Auth::login($user, $remember);

        // Обновляем последнюю активность
        $this->userRepository->updateLastActivity($user->id);

        return $user;
    }

    /**
     * Регистрация нового пользователя
     */
    public function register(array $userData): User
    {
        $userDTO = UserDTO::fromArray($userData);

        if (!$userDTO->validate()) {
            throw new \InvalidArgumentException('Некорректные данные пользователя');
        }

        if (!$userDTO->hasStrongPassword()) {
            throw new \InvalidArgumentException('Пароль должен содержать минимум 6 символов');
        }

        // Проверяем уникальность логина
        if ($this->userRepository->findByLogin($userDTO->login)) {
            throw new \InvalidArgumentException('Пользователь с таким логином уже существует');
        }

        return $this->userRepository->createUser($userDTO->getCreateData());
    }

    /**
     * Выход из системы
     */
    public function logout(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Проверить учетные данные
     */
    public function checkCredentials(string $login, string $password): bool
    {
        return Auth::attempt(['login' => $login, 'password' => $password]);
    }

    /**
     * Получить текущего пользователя
     */
    public function getCurrentUser(): ?User
    {
        return Auth::user();
    }

    /**
     * Проверить, авторизован ли пользователь
     */
    public function isAuthenticated(): bool
    {
        return Auth::check();
    }

    /**
     * Логирование входа
     */
    public function logLogin(User $user, Request $request): void
    {
        LoginLog::create([
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Изменить пароль пользователя
     */
    public function changePassword(User $user, string $newPassword): bool
    {
        $data = [
            'password' => Hash::make($newPassword),
            'save_password' => $newPassword,
        ];

        $this->userRepository->update($user->id, $data);
        return true;
    }

    /**
     * Проверить текущий пароль
     */
    public function verifyPassword(User $user, string $password): bool
    {
        return Hash::check($password, $user->password);
    }
}
