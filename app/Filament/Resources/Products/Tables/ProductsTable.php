<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->description(function ($record) {
                        if (! $record->is_recurring) return null;

                        $strategy = match ($record->price_strategy) {
                            'fixed' => 'Fijo',
                            'variable' => 'Variable',
                            'estimate' => 'Estimado',
                            default => $record->price_strategy
                        };

                        $message = "🔄 Recurrente ({$strategy})";

                        $message .= $record->expected_price ? "Valor esperado {$record->formatted_expected_price}" : '';
                        $message .= $record->payment_date ? " y pago el {$record->formatted_payment_date}" : '';

                        return $message;
                    }),
                TextColumn::make('price')
                    ->label('Precio')
                    ->getStateUsing(function ($record) {
                        return $record->formatted_price;
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('currency')
                    ->label('Moneda')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('defaultAccount.name')
                    ->label('Cuenta por defecto')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->searchable()
                    ->sortable()
                    ->boolean(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
