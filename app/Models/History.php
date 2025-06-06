<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $fillable = [
        'sourceable_id',
        'sourceable_type',
        'amount',
        'currency_id',
    ];

    public function sourceable()
    {
        return $this->morphTo();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}

