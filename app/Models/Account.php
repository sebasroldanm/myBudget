<?php

namespace App\Models;

use App\Services\ExchangeService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\NumberFormatterTrait;
use Illuminate\Support\Facades\Auth;

class Account extends Model
{
    use NumberFormatterTrait;

    private $exchangeService;

    protected $fillable = [
        'name',
        'type', // cash, bank, wallet, credit, investment
        'currency',
        'initial_balance',
        'current_balance',
        'credit_limit',
        'credit_available',
        'credit_interest_rate',
        'credit_due_date',
        'credit_payment_date',
        'is_active',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'credit_available' => 'decimal:2',
        'credit_interest_rate' => 'decimal:2',
        'credit_due_date' => 'date',
        'credit_payment_date' => 'date',
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

    private function getExchangeService(): ExchangeService
    {
        if (!$this->exchangeService) {
            $this->exchangeService = app(ExchangeService::class);
        }
        return $this->exchangeService;
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

    public function budgetItems(): HasMany
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

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Balance neto formateado
     */
    public function getFormattedBalanceAttribute()
    {
        return $this->formatCurrency($this->current_balance, $this->currency);
    }

    /**
     * Balance neto en otra moneda
     */
    public function getBalanceExchange($currency = null)
    {
        $exchangeService = $this->getExchangeService();

        $currency = $currency ?? $this->currency;
        if ($this->currency === $currency) {
            return $this->current_balance;
        }

        return $exchangeService->convert($this->currency, $currency, $this->current_balance);
    }

    /**
     * Balance neto en otra moneda formateado
     */
    public function getFormattedBalanceExchange($currency = null)
    {
        $currency = $currency ?? $this->currency;
        return $this->formatCurrency($this->getBalanceExchange($currency), $currency);
    }

    /**
     * Balance neto disponible para presupuestos
     */
    public function getBalanceBudgetsAttribute()
    {
        $exchangeService = $this->getExchangeService();

        $totalExpectedInAccountCurrency = $this->budgetItems->where('is_paid', false)->sum(function ($item) use ($exchangeService) {
            $amount = $item->expected_amount;
            $budgetCurrency = $item->budget->currency;
            $accountCurrency = $this->currency;

            if ($budgetCurrency === $accountCurrency) {
                return $amount;
            }

            return $exchangeService->convert($budgetCurrency, $accountCurrency, $amount);
        });
        return $this->current_balance - $totalExpectedInAccountCurrency;
    }

    /**
     * Balance neto disponible para presupuestos formateado
     */
    public function getFormattedBalanceBudgetsAttribute()
    {
        return $this->formatCurrency($this->balance_budgets, $this->currency);
    }

    /**
     * Balance neto disponible para presupuestos en otra moneda
     */
    public function getBalanceBudgetsExchange($currency = null)
    {
        $exchangeService = $this->getExchangeService();

        $currency = $currency ?? $this->currency;
        if ($this->currency === $currency) {
            return $this->balance_budgets;
        }

        return $exchangeService->convert($this->currency, $currency, $this->balance_budgets);
    }

    /**
     * Balance neto disponible para presupuestos en otra moneda formateado
     */
    public function getFormattedBalanceBudgetsExchange($currency = null)
    {
        $currency = $currency ?? $this->currency;
        return $this->formatCurrency($this->getBalanceBudgetsExchange($currency), $currency);
    }
}
