<?php

return [

    'obama' => [
        'cookie' => env('OBAMA_COOKIE'),
        'url' => env('OBAMA_URL', 'https://obama.ru'),
        'login_url' => env('OBAMA_LOGIN_URL', 'https://obama.ru/prmmxchngr'),
        'username' => env('OBAMA_USERNAME'),
        'password' => env('OBAMA_PASSWORD'),
        'pin' => env('OBAMA_PIN'),
        'auto_refresh_cookies' => env('OBAMA_AUTO_REFRESH_COOKIES', true),
    ],

    'ural' => [
        'cookie' => env('URAL_COOKIE'),
        'url' => env('URAL_URL', 'https://ural-obmen.ru'),
        'login_url' => env('URAL_LOGIN_URL', 'https://ural-obmen.ru/prmmxchngr'),
        'username' => env('URAL_USERNAME'),
        'password' => env('URAL_PASSWORD'),
        'pin' => env('URAL_PIN'),
        'auto_refresh_cookies' => env('URAL_AUTO_REFRESH_COOKIES', true),
    ]

];
