<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\NumberFormatterTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class Transaction extends Model
{
    use SoftDeletes, NumberFormatterTrait;

    const TYPE_INCOME = 'income';
    const TYPE_EXPENSE = 'expense';

    protected $fillable = [
        'account_id',
        'budget_item_id',
        'category_id',
        'transfer_id',
        'type',
        'amount',
        'is_locked',
        'transaction_date',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_locked' => 'boolean',
        'transaction_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('user_filter', function ($query) {
            if (Auth::check()) {
                $query->where('user_id', Auth::id());
            }
        });

        static::creating(function ($transaction) {
            if (Auth::check()) {
                $transaction->user_id = Auth::id();
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

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }

    public function budgetItem(): BelongsTo
    {
        return $this->belongsTo(BudgetItem::class);
    }

    public function lastedLog(): hasOne
    {
        return $this->hasOne(TransactionLog::class, 'transaction_id', 'id')->latest();
    }
    

    public function logs(): HasMany
    {
        return $this->hasMany(TransactionLog::class, 'transaction_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeIncome($query)
    {
        return $query->where('type', self::TYPE_INCOME);
    }

    public function scopeExpense($query)
    {
        return $query->where('type', self::TYPE_EXPENSE);
    }

    public function scopeLocked($query)
    {
        return $query->where('is_locked', true);
    }

    public function scopeUnlocked($query)
    {
        return $query->where('is_locked', false);
    }

    public function scopeCurrentMonth($query)
    {
        return $query->whereBetween('transaction_date', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    public function scopeCurrentYear($query)
    {
        return $query->whereBetween('transaction_date', [now()->startOfYear(), now()->endOfYear()]);
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    public function getFormattedAmountAttribute()
    {
        return $this->formatCurrency($this->amount, $this->account->currency);
    }
}
