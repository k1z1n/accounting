<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'yandex_webmaster' => [
        'oauth_token' => env('YANDEX_WEBMASTER_TOKEN'),
        'site_urls' => [
            'main_site' => env('YANDEX_WEBMASTER_SITE_1', 'https://palma-forum.io'),
            'landing' => env('YANDEX_WEBMASTER_SITE_2'),
            'blog' => env('YANDEX_WEBMASTER_SITE_3'),
            // Добавьте свои сайты
        ],
    ],

    'heleket' => [
        'obama' => [
            'balance_url'   => env('HELEKET_OBAMA_BALANCE_URL'),
            'history_url'   => env('HELEKET_OBAMA_HISTORY_URL'),
            'merchant_uuid' => env('HELEKET_OBAMA_MERCHANT_UUID'),
            'api_key'       => env('HELEKET_OBAMA_API_KEY'),
        ],
        'ural' => [
            'balance_url'   => env('HELEKET_URAL_BALANCE_URL'),
            'history_url'   => env('HELEKET_URAL_HISTORY_URL'),
            'merchant_uuid' => env('HELEKET_URAL_MERCHANT_UUID'),
            'api_key'       => env('HELEKET_URAL_API_KEY'),
        ],
    ],

    'rapira' => [
        'obama' => [
            'balance_url' => env('RAPIRA_OBAMA_BALANCE_URL'),
            'uid'         => env('RAPIRA_OBAMA_UID'),
            'private_key' => env('RAPIRA_OBAMA_PRIVATE_KEY'), // base64-encoded PEM
        ],
        'ural' => [
            'balance_url' => env('RAPIRA_URAL_BALANCE_URL'),
            'uid'         => env('RAPIRA_URAL_UID'),
            'private_key' => env('RAPIRA_URAL_PRIVATE_KEY'),
        ],
    ],

    'bybit' => [
        'main' => [
            'api_key' => env('BYBIT_API_KEY'),
            'secret_key' => env('BYBIT_SECRET_KEY'),
            'testnet' => env('BYBIT_TESTNET', false),
        ],
        'funding' => [
            'api_key' => env('BYBIT_FUNDING_API_KEY'),
            'secret_key' => env('BYBIT_FUNDING_SECRET_KEY'),
            'testnet' => env('BYBIT_TESTNET', false),
        ],
    ],

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),
    ],

];
