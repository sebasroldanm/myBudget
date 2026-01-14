<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\NumberFormatterTrait;

class Account extends Model
{
    use NumberFormatterTrait;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'currency',
        'initial_balance',
        'current_balance',
        'is_active',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
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

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class)->withTrashed();
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    public function getBalanceAttribute()
    {
        return $this->current_balance;
    }

    public function getFormattedBalanceAttribute()
    {
        return $this->formatCurrency($this->current_balance, $this->currency);
    }
}
