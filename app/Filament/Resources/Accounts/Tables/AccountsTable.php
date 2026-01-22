<?php

namespace App\Filament\Resources\Accounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),

                TextColumn::make('type')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'cash' => 'Efectivo',
                        'bank' => 'Banco',
                        'wallet' => 'Billetera',
                        'credit_card' => 'Tarjeta de Crédito',
                        'investment' => 'Inversión',
                        default => $state,
                    })
                    ->label('Tipo'),

                TextColumn::make('current_balance')
                    ->label('Saldo actual')
                    ->prefix(fn ($record) => $record ? "{$record->currency} $" : '$ ')
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.'),

                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Activa'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
