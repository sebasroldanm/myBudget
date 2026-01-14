<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\NumberFormatterTrait;
use Illuminate\Support\Facades\Auth;

class Account extends Model
{
    use NumberFormatterTrait;

    protected $fillable = [
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

    protected static function booted(): void
    {
        static::addGlobalScope('user_filter', function ($query) {
            if (Auth::check()) {
                $query->where('user_id', Auth::id());
            }
        });

        static::creating(function ($account) {
            if (Auth::check()) {
                $account->user_id = Auth::id();
            }
        });
    }
    
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
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
