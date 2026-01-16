<?php

namespace App\Filament\Resources\Budgets\Pages;

use App\Filament\Resources\Budgets\BudgetResource;
use App\Models\Budget;
use App\Services\BudgetService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditBudget extends EditRecord
{
    protected static string $resource = BudgetResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_items')
                ->label('Generar Items')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->action(function (BudgetResource $resource, Budget $record) {
                    $count = (new BudgetService)->generateBudgetItems($record);
                    Notification::make()
                        ->success()
                        ->title("$count Items generados")
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
