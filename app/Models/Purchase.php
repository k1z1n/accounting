<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'exchanger_id',
        'sale_amount',
        'sale_currency_id',
        'received_amount',
        'received_currency_id',
        'application_id',
    ];

    /**
     * Платформа (exchanger), откуда продают крипту.
     */
    public function exchanger()
    {
        return $this->belongsTo(Exchanger::class, 'exchanger_id');
    }

    /**
     * Валюта «Продажа» (sale_amount).
     */
    public function saleCurrency()
    {
        return $this->belongsTo(Currency::class, 'sale_currency_id');
    }

    /**
     * Валюта «Получено» (received_amount).
     */
    public function receivedCurrency()
    {
        return $this->belongsTo(Currency::class, 'received_currency_id');
    }

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }
}
