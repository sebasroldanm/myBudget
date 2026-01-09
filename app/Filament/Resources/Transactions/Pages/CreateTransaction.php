<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;
        return $data;
    }

    protected function afterCreate(): void
    {
        $transaction = $this->record;
        $account = $transaction->account;

        if ($transaction->type === 'income') {
            $account->increment('current_balance', $transaction->amount);
        } else {
            $account->decrement('current_balance', $transaction->amount);
        }
    }
}
