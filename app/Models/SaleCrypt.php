<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleCrypt extends Model
{
    protected $fillable = [
        'user_id',
        'application_id',
        'exchanger_id',
        'sale_amount',
        'sale_currency_id',
        'fixed_amount',
        'fixed_currency_id',
        'buy_amount',
        'buy_currency_id',
    ];

    // Связь с платформой (exchanger)
    public function exchanger(): BelongsTo
    {
        return $this->belongsTo(Exchanger::class);
    }

    // Валюта, в которой указана сумма продажи
    public function saleCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'sale_currency_id');
    }

    // Валюта, в которой указан фикс
    public function fixedCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'fixed_currency_id');
    }

    // Валюта, в которой указана сумма покупки
    public function buyCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'buy_currency_id');
    }

    // Связь с заявкой
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'application_id');
    }
}
