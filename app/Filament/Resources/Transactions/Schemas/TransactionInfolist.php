<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Traits\NumberFormatterTrait;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class TransactionInfolist
{
    use NumberFormatterTrait;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([
                Section::make('Detalles de la Transacción')
                    ->icon('heroicon-o-document-text')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('amount')
                                ->label('Monto')
                                ->weight(FontWeight::Bold)
                                ->size(TextSize::Large)
                                ->state(fn($record) => self::formatCurrency((float)$record->amount, $record->account->currency)),

                            TextEntry::make('type')
                                ->label('Tipo')
                                ->badge()
                                ->formatStateUsing(fn(string $state): string => match ($state) {
                                    'income' => 'Ingreso',
                                    'expense' => 'Gasto',
                                    default => $state,
                                })
                                ->color(fn(string $state): string => match ($state) {
                                    'income' => 'success',
                                    'expense' => 'danger',
                                    default => 'gray',
                                }),

                            TextEntry::make('transaction_date')
                                ->label('Fecha')
                                ->date('d/m/Y'),
                            
                            TextEntry::make('account.name')
                                ->label('Cuenta'),
                                
                            TextEntry::make('category.name')
                                ->label('Categoría'),

                            TextEntry::make('is_locked')
                                ->label('Bloqueado')
                                ->badge()
                                ->color(fn(bool $state) => $state ? 'danger' : 'success')
                                ->formatStateUsing(fn(bool $state) => $state ? 'Sí' : 'No'),
                        ]),

                        TextEntry::make('description')
                            ->label('Descripción')
                            ->state(fn($record) => $record->description ?? 'Sin descripción')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
