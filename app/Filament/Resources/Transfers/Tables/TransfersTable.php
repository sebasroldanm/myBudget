<?php

namespace App\Filament\Resources\Transfers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransfersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transfer_type')
                    ->label('Transferencia')
                    ->state(function ($record) {
                        $from = $record->fromAccount->name ?? '?';
                        $to = $record->toAccount->name ?? '?';
                        return "🔄 Transferencia: {$from} ➔ {$to}";
                    }),

                TextColumn::make('amount')
                    ->money(fn ($record) => $record->from_currency),

                TextColumn::make('amount_converted')
                    ->label('Recibido')
                    ->money(fn ($record) => $record->to_currency),

                TextColumn::make('exchange_rate')
                    ->label('Tasa'),

                TextColumn::make('transfer_date')
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
