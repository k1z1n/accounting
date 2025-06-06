<?php

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
            fn (Request $r) => route('view.main')
        );


        $middleware->alias([
            'admin' => IsAdmin::class,
//            'user' => IsUser::class,
//            'guest_user' => IsGuest::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
