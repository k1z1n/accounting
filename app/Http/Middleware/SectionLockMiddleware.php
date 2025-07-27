<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SectionLockMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $chosen = session('chosen_section');
        $route = $request->route();
        $current = null;

        // Определяем, к какому разделу относится текущий маршрут
        if ($route && $route->getName()) {
            if (str_starts_with($route->getName(), 'applications')) {
                $current = 'applications';
            } elseif (str_starts_with($route->getName(), 'admin.dashboard')) {
                $current = 'dashboard';
            } // Добавьте другие разделы по необходимости
        }

        // Исключения: страница выбора и logout
        $isChoose = $route && in_array($route->getName(), ['choose.page', 'choose.section']);
        $isLogout = $route && $route->getName() === 'logout';

        if ($chosen && $current && $chosen !== $current && !$isChoose && !$isLogout) {
            return redirect()->route('choose.page')->with('error', 'Сначала завершите работу в текущем разделе.');
        }

        return $next($request);
    }
}
