<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireSectionChoice
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
        // Проверяем, авторизован ли пользователь
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        $chosenSection = session('chosen_section');

        // Если раздел не выбран, редиректим на страницу выбора
        if (!$chosenSection) {
            return redirect()->route('choose.page');
        }

        // Проверяем права доступа к выбранному разделу
        if ($chosenSection === 'applications' && $user->role !== 'admin') {
            session()->forget('chosen_section');
            return redirect()->route('choose.page')->with('error', 'Доступ к заявкам только для администраторов');
        }

        if ($chosenSection === 'dashboard' && $user->role !== 'admin') {
            session()->forget('chosen_section');
            return redirect()->route('choose.page')->with('error', 'Доступ к дашборду только для администраторов');
        }



        return $next($request);
    }
}
