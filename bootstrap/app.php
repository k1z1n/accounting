<?php

use App\Http\Middleware\CheckPlatform;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsGuest;
use App\Http\Middleware\IsUser;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->redirectGuestsTo(
            fn (Request $r) => route('view.login')
        );

        $middleware->redirectUsersTo(
            fn (Request $r) => route('applications.index')
        );


        $middleware->alias([
            'admin' => IsAdmin::class,
            'platform' => CheckPlatform::class,
//            'user' => IsUser::class,
//            'guest_user' => IsGuest::class,
            'section.lock' => \App\Http\Middleware\SectionLockMiddleware::class,
            'section.choice' => \App\Http\Middleware\RequireSectionChoice::class,
        ]);
        // Убираем глобальное применение section.lock
        // $middleware->web([
        //     'section.lock',
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
