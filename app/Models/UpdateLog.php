<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpdateLog extends Model
{
    protected $fillable = [
        'user_id',
        'sourceable_id',
        'sourceable_type',
        'update',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Полиморфная связь к редактируемой сущности */
    public function sourceable()
    {
        return $this->morphTo();
    }
}
