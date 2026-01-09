<?php

namespace App\Filament\Resources\Transactions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_date')
                    ->label('Fecha')
                    ->sortable()
                    ->date(),

                TextColumn::make('account.name')
                    ->label('Cuenta'),

                TextColumn::make('category.name')
                    ->label('Categoría'),

                TextColumn::make('amount')
                    ->label('Monto')
                    ->money(fn ($record) => $record->account->currency),

                TextColumn::make('type')
                    ->badge()
                    ->sortable()
                    ->colors([
                        'success' => 'income',
                        'danger' => 'expense',
                    ]),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->before(function ($record) {
                        $account = $record->account;
                        if ($record->type === 'income') {
                            $account->decrement('current_balance', $record->amount);
                        } else {
                            $account->increment('current_balance', $record->amount);
                        }
                    }),
                RestoreAction::make()
                    ->before(function ($record) {
                        $account = $record->account;
                        if ($record->type === 'income') {
                            $account->increment('current_balance', $record->amount);
                        } else {
                            $account->decrement('current_balance', $record->amount);
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
