<?php

namespace App\Filament\Resources\Budgets\Tables;

use App\Models\Budget;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BudgetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Presupuesto')
                    ->searchable(),

                TextColumn::make('currency')
                    ->label('Divisa'),

                TextColumn::make('period_start')
                    ->label('Desde')
                    ->date(),

                TextColumn::make('period_end')
                    ->label('Hasta')
                    ->date(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->getStateUsing(function ($record) {
                        return match ($record->status) {
                            'draft' => '✏️ Borrador',
                            'active' => '✅ Activo',
                            'inactive' => '❌ Inactivo',
                            'locked' => '🔒 Bloqueado',
                        };
                    }),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                        'locked' => 'Bloqueado',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(function (Budget $record) {
                        return $record->status !== 'locked';
                    }),
                ViewAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // RestoreAction::make(),
                ]),
            ]);
    }
}
