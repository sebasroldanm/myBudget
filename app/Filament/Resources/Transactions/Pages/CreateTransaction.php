<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\BudgetItem;
use App\Services\BudgetService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use App\Services\TransactionService;
use Carbon\Carbon;

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
        app(TransactionService::class)->handleAfterCreate($this->record);

        if ($this->record->budget_item_id) {
            app(BudgetService::class)->updateBudgetItem($this->record);
        }
    }
}
