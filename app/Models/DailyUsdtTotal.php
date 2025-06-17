<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyUsdtTotal extends Model
{
    protected $table = 'daily_usdt_totals';
    protected $fillable =
    [
        'date',
        'total',
        'delta'
    ];
}
