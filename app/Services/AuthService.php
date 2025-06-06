<?php

namespace App\Services;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Регистрирует нового пользователя, сохраняет registered_at
     */
    public function register(RegisterRequest $request): User
    {
        $data = $request->validated();
        $data['save_password'] = $data['password'];
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        $user->registered_at = now();
        $user->save();

        return $user;
    }

    /**
     * Вход: проверка учётных, регенерация сессии, запись лога
     * @throws AuthenticationException
     */
    public function login(LoginRequest $request): User
    {
        $credentials = $request->validated();

        if (!auth()->attempt(
            ['login' => $credentials['login'], 'password' => $credentials['password']],
            $credentials['remember'] ?? false
        )) {
            throw new AuthenticationException('Неверные учётные данные');
        }

        $user = auth()->user();
        $request->session()->regenerate();

        LoginLog::create([
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return $user;
    }

    /**
     * Выход из системы
     */
    public function logout(\Illuminate\Http\Request $request): void
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
