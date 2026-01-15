<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class TransactionLogService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function log(Transaction $transaction, string $event, array $meta = []): void
    {
        $transaction->loadMissing(['account', 'product', 'category']);

        $account = $transaction->account;

        $balanceAfter = $account->current_balance;
        $balanceBefore = $balanceAfter - $transaction->amount;

        TransactionLog::create([
            'transaction_id'   => $transaction->id,
            'event'            => $event,
            'event_at'         => now(),

            // Transaction snapshot
            'transaction_type' => $transaction->type,
            'amount'           => $transaction->amount,
            'currency_amount'  => $account->currency,
            'transaction_date' => $transaction->transaction_date,
            'is_locked'        => $transaction->is_locked ?? false,
            'transaction_description' => $transaction->description,

            // Account snapshot
            'account_id'       => $transaction->account_id,
            'account_name'     => $account->name,
            'account_type'     => $account->type,
            'account_currency' => $account->currency,

            // Balance snapshot
            'balance_before'   => $balanceBefore,
            'balance_after'    => $balanceAfter,
            'currency_balance_before' => $account->currency,
            'currency_balance_after' => $account->currency,

            // Product snapshot
            'product_id'             => $transaction->product_id,
            'product_name'           => $transaction->product?->name,
            'product_vendor'         => $transaction->product?->vendor,
            'product_currency'       => $transaction->product?->currency,
            'product_is_recurring'   => $transaction->product?->is_recurring,
            'product_price_strategy' => $transaction->product?->price_strategy,
            'product_price'          => $transaction->product?->price,
            'product_expected_price' => $transaction->product?->expected_price,

            // Category snapshot
            'category_id'      => $transaction->category_id,
            'category_name'    => $transaction->category?->name,

            // Request context
            'user_id'          => Auth::user()->id,
            'user_agent'       => Request::userAgent(),
            'ip_address'       => Request::ip(),
            'meta'             => $meta,
        ]);
    }
}
