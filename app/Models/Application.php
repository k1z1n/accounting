<?php
// app/Models/Application.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    protected $fillable = [
        'app_id',
        'app_created_at',
        'exchanger',
        'status',
        'sale_text',
        'sell_amount',
        'sell_currency_id',
        'buy_amount',
        'buy_currency_id',
        'expense_amount',
        'expense_currency_id',
        'merchant',
        'order_id',
        'user_id',
    ];

    /**
     * Связь «привязка продажи» — внешний ключ sell_currency_id → currencies.id
     */
    public function sellCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'sell_currency_id');
    }
    public function buyCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'buy_currency_id');
    }
    public function expenseCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'expense_currency_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function histories()
    {
        return $this->hasMany(History::class);
    }

}
