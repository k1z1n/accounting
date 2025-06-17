<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::command('app:calculate-daily-usdt-summary')
    ->everyMinute()
    ->timezone('Europe/Moscow')        ->appendOutputTo(storage_path('logs/daily-summary.log'));
