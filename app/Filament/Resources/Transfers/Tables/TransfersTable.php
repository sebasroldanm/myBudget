<?php

namespace App\Filament\Resources\Transfers\Tables;

use App\Traits\NumberFormatterTrait;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransfersTable
{
    use NumberFormatterTrait;

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
                    ->label('Enviado')
                    ->formatStateUsing(function ($record, $state) {
                        return (new self())->formatCurrency(
                            (float) $state, 
                            $record->fromAccount->currency
                        );
                    }),

                TextColumn::make('amount_converted')
                    ->label('Recibido')
                    ->formatStateUsing(function ($record, $state) {
                        return (new self())->formatCurrency(
                            (float) $state, 
                            $record->toAccount->currency
                        );
                    }),

                TextColumn::make('exchange_rate')
                    ->label('Tasa')
                    ->formatStateUsing(function ($record, $state) {
                        return (new self())->formatCurrency(
                            (float) $state
                        );
                    }),

                TextColumn::make('transfer_date')
                    ->label('Fecha')
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
