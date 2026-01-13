<?php

namespace App\Filament\Resources\ExchangeRates\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExchangeRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('from_currency')
                    ->label('Divisa origen')
                    ->options([
                        'USD' => 'USD',
                        'COP' => 'COP',
                        'EUR' => 'EUR',
                    ])
                    ->live()
                    ->required(),

                Select::make('to_currency')
                    ->label('Divisa destino')
                    ->options([
                        'USD' => 'USD',
                        'COP' => 'COP',
                        'EUR' => 'EUR',
                    ])
                    ->required()
                    ->different('from_currency')
                    ->reactive()
                    ->validationMessages([
                        'different' => 'La divisa de destino debe ser diferente a la de origen.',
                    ]),

                TextInput::make('rate')
                    ->label('Tasa de conversión')
                    ->numeric()
                    ->required()
                    ->rule('gt:0'),

                DateTimePicker::make('retrieved_at')
                    ->label('Fecha de obtención')
                    ->default(now())
                    ->required(),
            ]);
    }
}
