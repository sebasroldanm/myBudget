<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function handleAfterCreate(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $account = $transaction->account;

            if ($transaction->type === 'income') {
                $account->increment('current_balance', $transaction->amount);
            } else {
                $account->decrement('current_balance', $transaction->amount);
            }
        });
    }
    
    public function createTransaction(array $data): Transaction
    {
        $transaction = Transaction::create($data);
        $this->handleAfterCreate($transaction);

        return $transaction;
    }

    public function updateTransaction(Transaction $transaction, array $newData): Transaction
    {
        return DB::transaction(function () use ($transaction, $newData) {
            $this->applyBalance($transaction, reverse: true);
            $transaction->update($newData);
            $this->applyBalance($transaction, reverse: false);

            return $transaction;
        });
    }

    public function deleteTransaction(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $this->applyBalance($transaction, true);
            $transaction->delete();
        });
    }

    public function restoreTransaction(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $this->applyBalance($transaction, false);
            $transaction->restore();
        });
    }

    private function applyBalance(Transaction $transaction, bool $reverse): void
    {
        $account = $transaction->account;
        $amount = $transaction->amount;

        if ($reverse) {
            if ($transaction->type === 'income') {
                $account->decrement('current_balance', $amount);
            } else {
                $account->increment('current_balance', $amount);
            }
        } else {
            if ($transaction->type === 'income') {
                $account->increment('current_balance', $amount);
            } else {
                $account->decrement('current_balance', $amount);
            }
        }
    }

    public function reverse(Transaction $transaction): void
    {
        $account = $transaction->account;

        if ($transaction->type === 'income') {
            $account->decrement('current_balance', $transaction->amount);
        } else {
            $account->increment('current_balance', $transaction->amount);
        }
    }

    public function apply(Transaction $transaction): void
    {
        $account = $transaction->account;

        if ($transaction->type === 'income') {
            $account->increment('current_balance', $transaction->amount);
        } else {
            $account->decrement('current_balance', $transaction->amount);
        }
    }
}
