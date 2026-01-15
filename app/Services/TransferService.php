<?php

namespace App\Services;

use App\Models\Account;
use App\Models\ExchangeRate;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Services\TransactionService;
use App\Traits\NumberFormatterTrait;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransferService
{
    use NumberFormatterTrait;
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function createTransfer(array $data): Transfer
    {
        return DB::transaction(function () use ($data) {
            $preparedData = $this->prepareTransferData($data);
            $transfer = Transfer::create($preparedData);

            $transactionService = app(TransactionService::class);
            $expenseData = [
                'user_id' => $preparedData['user_id'],
                'account_id' => $preparedData['from_account_id'],
                'transfer_id' => $transfer->id,
                'type' => Transaction::TYPE_EXPENSE,
                'amount' => $preparedData['amount'],
                'is_locked' => true,
                'transaction_date' => $preparedData['transfer_date'],
                'description' => 'Transferencia enviada a ' . $transfer->toAccount->name . 'Descripción: ' . ($preparedData['note'] ?? 'Transferencia enviada a ' . $transfer->toAccount->name),
            ];
            $transactionService->createTransaction($expenseData);

            $incomeData = [
                'user_id' => $preparedData['user_id'],
                'account_id' => $preparedData['to_account_id'],
                'transfer_id' => $transfer->id,
                'type' => Transaction::TYPE_INCOME,
                'amount' => $preparedData['amount_converted'],
                'transaction_date' => $preparedData['transfer_date'],
                'is_locked' => true,
                'description' => 'Transferencia recibida de ' . $transfer->fromAccount->name . 'Descripción: ' . ($preparedData['note'] ?? 'Transferencia recibida de ' . $transfer->fromAccount->name),
            ];
            $transactionService->createTransaction($incomeData);

            return $transfer;
        });
    }

    public function prepareTransferData(array $data): array
    {
        $fromAccount = Account::findOrFail($data['from_account_id']);
        $toAccount = Account::findOrFail($data['to_account_id']);

        $data['user_id'] = Auth::id();
        $data['from_currency'] = $fromAccount->currency;
        $data['to_currency'] = $toAccount->currency;

        if ($fromAccount->currency === $toAccount->currency) {
            $data['exchange_rate'] = 1;
            $data['amount_converted'] = $data['amount'];
        } else {
            $rate = $this->getLatestRate($fromAccount->currency, $toAccount->currency);
            $data['exchange_rate'] = $rate;
            $data['amount_converted'] = bcmul($data['amount'], (string)$rate, 2);
        }

        if ($fromAccount->current_balance < $data['amount']) {
            $pay = $this->formatCurrency($data['amount'], $fromAccount->currency);
            $balance = $this->formatCurrency($fromAccount->current_balance, $fromAccount->currency);

            throw ValidationException::withMessages([
                'amount' => "Saldo insuficiente. La cuenta {$fromAccount->name} tiene {$balance} y estás intentando transferir {$pay}.",
            ]);
        }

        return $data;
    }

    private function getLatestRate(string $from, string $to): float
    {
        $rate = ExchangeRate::where('from_currency', $from)
            ->where('to_currency', $to)
            ->latest('retrieved_at')
            ->value('rate');

        if (!$rate) {
            throw new Exception("No existe tasa de conversión disponible de {$from} a {$to}");
        }

        return (float) $rate;
    }
}
