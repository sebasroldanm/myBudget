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
                    ->options([
                        'cash' => 'Efectivo',
                        'bank' => 'Banco',
                        'wallet' => 'Billetera',
                        'credit' => 'Crédito',
                        'investment' => 'Inversión',
                    ])
                    ->helperText(function ($record) {
                        if ($record && $record->transactions()->exists()) {
                            return 'No modificable, existen movimientos.';
                        }
                        return null;
                    })
                    ->disabled(function ($record) {
                        if (! $record) {
                            return false;
                        }
                        return $record->transactions()->count() > 0;
                    })
                    ->required(),

                Select::make('currency')
                    ->label('Moneda')
                    ->options([
                        'COP' => 'COP',
                        'USD' => 'USD',
                        'EUR' => 'EUR',
                    ])
                    ->default(fn () => Auth::user()->currency_default)
                    ->helperText(function ($record) {
                        if ($record && $record->transactions()->exists()) {
                            return 'No modificable, existen movimientos.';
                        }
                        return null;
                    })
                    ->disabled(function ($record) {
                        if (! $record) {
                            return false;
                        }
                        return $record->transactions()->count() > 0;
                    })
                    ->required(),

                TextInput::make('initial_balance')
                    ->label('Saldo inicial')
                    ->prefix('$')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->numeric()
                    ->default(0)
                    ->trim()
                    ->helperText(function ($record) {
                        if ($record && $record->transactions()->exists()) {
                            return 'No modificable, existen movimientos.';
                        }
                        return null;
                    })
                    ->extraInputAttributes([
                        'onfocus' => "if(this.value == '0') this.value = ''",
                        'onblur' => "if(this.value == '') this.value = '0'",
                        ])
                    ->disabled(function ($record) {
                        if (! $record) {
                            return false;
                        }
                        return $record->transactions()->count() > 0;
                    })
                    ->required(),

                Toggle::make('is_active')
                    ->label('Activa')
                    ->default(true),
            ]);
    }
}
