<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $old = $this->record->replicate();

        // Revertir movimiento anterior
        DB::transaction(function () use ($old, $data) {
            $this->applyReverse($old);
            $this->record->update($data);
            $this->apply($this->record);
        });

        return $this->record->getAttributes();
    }

    protected function applyReverse($transaction): void
    {
        $account = $transaction->account;

        if ($transaction->type === 'income') {
            $account->decrement('current_balance', $transaction->amount);
        } else {
            $account->increment('current_balance', $transaction->amount);
        }
    }

    protected function apply($transaction): void
    {
        $account = $transaction->account;

        if ($transaction->type === 'income') {
            $account->increment('current_balance', $transaction->amount);
        } else {
            $account->decrement('current_balance', $transaction->amount);
        }
    }
}
