<?php

namespace App\Filament\Resources\Transfers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TransferInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalles de la Transferencia')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('transfer_date')
                                    ->label('Fecha')
                                    ->date(),
                                TextEntry::make('fromAccount.name')
                                    ->label('Origen'),
                                TextEntry::make('toAccount.name')
                                    ->label('Destino'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('amount')
                                    ->label('Monto Enviado')
                                    ->money(fn($record) => $record->from_currency),
                                TextEntry::make('amount_converted')
                                    ->label('Monto Recibido')
                                    ->money(fn($record) => $record->to_currency),
                            ]),
                        TextEntry::make('note')
                            ->label('Nota')
                            ->placeholder('Sin descripción'),
                    ])
            ]);
    }
}
