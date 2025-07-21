<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'exchanger_id',
        'user_id',
        'sell_amount',
        'sell_currency_id',
        'comment',
    ];

    public function exchanger(): BelongsTo
    {
        return $this->belongsTo(Exchanger::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sellCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'sell_currency_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'sell_currency_id');
    }
}
