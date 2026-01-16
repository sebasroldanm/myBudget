<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\NumberFormatterTrait;
use Illuminate\Support\Facades\Auth;

class Product extends Model
{
    use SoftDeletes, NumberFormatterTrait;

    protected $fillable = [
        'category_id',
        'vendor',
        'name',
        'is_recurring',
        'payment_date',
        'periodicity',
        'start_date',
        'end_date',
        'currency',
        'default_account_id',
        'price_strategy',
        'price',
        'expected_price',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
        'payment_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'price' => 'decimal:2',
        'expected_price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('user_filter', function ($query) {
            if (Auth::check()) {
                $query->where('user_id', Auth::id());
            }
        });

        static::creating(function ($product) {
            if (Auth::check()) {
                $product->user_id = Auth::id();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function defaultAccount()
    {
        return $this->belongsTo(Account::class, 'default_account_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function budgetItems()
    {
        return $this->hasMany(BudgetItem::class);
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

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeInDateRange($query, $date)
    {
        return $query->where(function ($q) use ($date) {
            $q->whereNull('start_date')
              ->orWhere('start_date', '<=', $date);
        })->where(function ($q) use ($date) {
            $q->whereNull('end_date')
              ->orWhere('end_date', '>=', $date);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    public function getFormattedPriceAttribute()
    {
        return $this->formatCurrency($this->price, $this->currency);
    }

    public function getFormattedExpectedPriceAttribute()
    {
        if (!$this->expected_price) {
            return null;
        }
        return $this->formatCurrency($this->expected_price, $this->currency);
    }

    public function getFormattedPaymentDateAttribute()
    {
        if (!$this->payment_date) {
            return null;
        }
        return $this->payment_date?->format('d/m/Y');
    }

}
