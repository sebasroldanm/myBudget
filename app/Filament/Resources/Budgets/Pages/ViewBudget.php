<?php

namespace App\Filament\Resources\Budgets\Pages;

use App\Filament\Resources\Budgets\BudgetResource;
use App\Models\Budget;
use App\Services\BudgetService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewBudget extends ViewRecord
{
    protected static string $resource = BudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_items')
                ->label('Generar Items')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->visible(function (Budget $record) {
                    return in_array($record->status, ['draft', 'active']);
                })
                ->action(function (Budget $record) {
                    $count = (new BudgetService)->generateBudgetItems($record);
                    Notification::make()
                        ->success()
                        ->title("$count Items generados")
                        ->send();
                }),
            Action::make('active')
                ->label('Activar')
                ->icon('heroicon-o-check-circle')
                ->visible(function (Budget $record) {
                    return in_array($record->status, ['draft', 'inactive']);
                })
                ->action(function (Budget $record) {
                    $record->status = 'active';
                    $record->save();
                    Notification::make()
                        ->success()
                        ->title('Budget activado')
                        ->send();
                }),
            Action::make('inactive')
                ->label('Inactivar')
                ->icon('heroicon-o-x-circle')
                ->visible(function (Budget $record) {
                    return $record->status === 'active';
                })
                ->action(function (Budget $record) {
                    $record->status = 'inactive';
                    $record->save();
                    Notification::make()
                        ->success()
                        ->title('Budget inactivado')
                        ->send();
                }),
            Action::make('lock')
                ->label('Bloquear')
                ->icon('heroicon-o-lock-closed')
                ->visible(function (Budget $record) {
                    return $record->status === 'active';
                })
                ->action(function (Budget $record) {
                    $record->status = 'locked';
                    $record->save();
                    Notification::make()
                        ->success()
                        ->title('Budget bloqueado')
                        ->send();
                }),
            Action::make('unlock')
                ->label('Desbloquear')
                ->icon('heroicon-o-lock-open')
                ->visible(function (Budget $record) {
                    return $record->status === 'locked';
                })
                ->action(function (Budget $record) {
                    $record->status = 'active';
                    $record->save();
                    Notification::make()
                        ->success()
                        ->title('Budget desbloqueado')
                        ->send();
                }),
            EditAction::make()
                ->visible(function (Budget $record) {
                    return $record->status !== 'locked';
                }),
        ];
    }
}
