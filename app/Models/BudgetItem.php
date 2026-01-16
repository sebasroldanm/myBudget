<?php

namespace App\Models;

use App\Traits\NumberFormatterTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetItem extends Model
{
    use HasFactory, NumberFormatterTrait;

    protected $fillable = [
        'budget_id',
        'product_id',
        'expected_amount',
        'actual_amount',
        'payment_date',
        'pay_date',
        'is_paid',
        'account_id',
        'notes',
    ];

    protected $casts = [
        'expected_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'payment_date' => 'date',
        'pay_date' => 'date',
        'is_paid' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    public function getFormattedExpectedAmountAttribute()
    {
        // Assuming budget has currency, or product has currency. 
        // For now, let's use the budget's currency if available via relation, or fallback.
        $currency = $this->budget->currency ?? 'COP'; 
        return $this->formatCurrency($this->expected_amount, $currency);
    }
    
    public function getFormattedActualAmountAttribute()
    {
        $currency = $this->budget->currency ?? 'COP';
        return $this->formatCurrency($this->actual_amount, $currency);
    }
}
