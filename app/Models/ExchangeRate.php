<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = [
        'from_currency',
        'to_currency',
        'rate',
        'retrieved_at',
    ];

    protected $casts = [
        'rate' => 'decimal:8',
        'retrieved_at' => 'datetime',
    ];
}
