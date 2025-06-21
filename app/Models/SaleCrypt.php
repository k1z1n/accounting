<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleCrypt extends Model
{
    protected $fillable = [
        'exchanger_id',
        'sale_amount',
        'sale_currency_id',
        'fixed_amount',
        'fixed_currency_id',
        'application_id',
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


    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }
}
