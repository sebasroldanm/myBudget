<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transfer extends Model
{
    protected $fillable = [
        'user_id',
        'from_account_id',
        'to_account_id',
        'amount',
        'from_currency',
        'to_currency',
        'exchange_rate',
        'amount_converted',
        'transfer_date',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_converted' => 'decimal:2',
        'exchange_rate' => 'decimal:8',
        'transfer_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeCurrentMonth($query)
    {
        return $query->whereBetween('transfer_date', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    public function scopeCurrentYear($query)
    {
        return $query->whereBetween('transfer_date', [now()->startOfYear(), now()->endOfYear()]);
    }
}
