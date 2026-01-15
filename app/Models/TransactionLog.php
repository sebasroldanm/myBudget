<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionLog extends Model
{
    use HasUlids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'transaction_id',
        'event',
        'event_at',
        'transaction_type',
        'amount',
        'currency_amount',
        'transaction_date',
        'is_locked',
        'transaction_description',
        'account_id',
        'account_name',
        'account_type',
        'account_currency',
        'balance_before',
        'balance_after',
        'currency_balance_before',
        'currency_balance_after',
        'product_id',
        'product_name',
        'product_vendor',
        'product_currency',
        'product_is_recurring',
        'product_price_strategy',
        'product_price',
        'product_expected_price',
        'user_id',
        'user_agent',
        'ip_address',
        'meta',
    ];

    protected $casts = [
        'event_at' => 'datetime',
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'product_price' => 'decimal:2',
        'product_expected_price' => 'decimal:2',
        'is_locked' => 'boolean',
        'product_is_recurring' => 'boolean',
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
