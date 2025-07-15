<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlatform
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
        public function handle(Request $request, Closure $next): Response
    {
        // Проверяем, авторизован ли пользователь
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        $currentPlatform = session('app');

        // Проверяем, есть ли выбранная платформа в сессии
        if (!$currentPlatform) {
            // Если это уже страница выбора платформы, пропускаем
            if ($request->routeIs('platform.*')) {
                return $next($request);
            }

            // Редиректим на страницу выбора платформы
            return redirect()->route('platform.select');
        }

        // Проверяем, имеет ли пользователь доступ к выбранной платформе
        if (!$user->hasAccessToPlatform($currentPlatform)) {
            // Очищаем недопустимую платформу из сессии
            session()->forget('app');

            return redirect()->route('platform.select')
                ->with('error', 'У вас нет доступа к выбранной платформе. Пожалуйста, выберите доступную платформу.');
        }

        // Дополнительная проверка для роли статистика
        if ($user->isStatistician() && $currentPlatform === 'accounting') {
            session()->forget('app');
            return redirect()->route('platform.select')
                ->with('error', 'Статистики имеют доступ только к платформе "Статистика".');
        }

        // Дополнительная проверка для роли бухгалтера
        if ($user->isAccountant() && $currentPlatform === 'statistics') {
            session()->forget('app');
            return redirect()->route('platform.select')
                ->with('error', 'Бухгалтеры имеют доступ только к платформе "Бухгалтерия".');
        }

        return $next($request);
    }
}
