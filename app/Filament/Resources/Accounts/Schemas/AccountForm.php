<?php

namespace App\Filament\Resources\Accounts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Auth;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre de la cuenta')
                    ->required()
                    ->maxLength(100),

                Select::make('type')
                    ->label('Tipo de cuenta')
                    ->required()
                    ->options([
                        'cash' => 'Efectivo',
                        'bank' => 'Banco',
                        'wallet' => 'Billetera',
                        'credit' => 'Crédito',
                        'investment' => 'Inversión',
                    ]),

                Select::make('currency')
                    ->label('Moneda')
                    ->options([
                        'COP' => 'COP',
                        'USD' => 'USD',
                        'EUR' => 'EUR',
                    ])
                    ->default(fn () => Auth::user()->currency_default)
                    ->required(),

                TextInput::make('initial_balance')
                    ->label('Saldo inicial')
                    ->prefix('$')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->numeric()
                    ->default(0)
                    ->trim()
                    ->helperText('Solo se usa al crear la cuenta')
                    ->extraInputAttributes([
                        'onfocus' => "if(this.value == '0') this.value = ''",
                        'onblur' => "if(this.value == '') this.value = '0'",
                        ])
                    // TODO: Implementar si no existen movimientos | $record->transactions()->count() === 0
                    ->disabled(function ($record) {
                        if (! $record) {
                            return false;
                        }
                        return $record->initial_balance !== $record->current_balance;
                    })
                    ->required(),

                Toggle::make('is_active')
                    ->label('Activa')
                    ->default(true),
            ]);
    }
}
