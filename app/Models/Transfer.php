<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transfer extends Model
{
    protected $fillable = [
        'exchanger_from_id',
        'exchanger_to_id',
        'commission',
        'commission_id',
        'amount',
        'amount_id',
    ];

    public function exchangerFrom(): BelongsTo
    {
        return $this->belongsTo(Exchanger::class, 'exchanger_from_id');
    }

    public function exchangerTo(): BelongsTo
    {
        return $this->belongsTo(Exchanger::class, 'exchanger_to_id');
    }

    public function commissionCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'commission_id');
    }

    public function amountCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'amount_id');
    }
}
