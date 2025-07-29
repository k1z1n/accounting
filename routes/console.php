<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::command('app:calculate-daily-usdt-summary')
    ->dailyAt('23:55')
    ->timezone('Europe/Moscow')->appendOutputTo(storage_path('logs/daily-summary.log'));;

// Отправка балансов обменников в Telegram каждый час
Schedule::command('telegram:send-balances')
    ->hourly()
    ->timezone('Europe/Moscow')
    ->appendOutputTo(storage_path('logs/telegram-balances.log'));
