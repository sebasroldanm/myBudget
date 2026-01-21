<?php

namespace App\Filament\Resources\Accounts\Schemas;

use App\Models\Account;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Auth;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Cuenta')
                    ->description('Información general de la cuenta')
                    ->icon(Heroicon::BuildingLibrary)
                    ->columns(3)
                    ->schema([
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
                                'credit_card' => 'Tarjeta de Crédito',
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
                            ->live()
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Activo')
                            ->onColor('success')
                            ->offColor('danger')
                            ->onIcon(Heroicon::CheckCircle)
                            ->offIcon(Heroicon::XCircle)
                            ->inline(false)
                            ->default(true),

                    ]),

                Section::make('Finanzas')
                    ->description('Información financiera de la cuenta')
                    ->icon(Heroicon::Banknotes)
                    ->columns(3)
                    ->schema([
                        Select::make('currency')
                            ->label('Moneda')
                            ->options([
                                'COP' => 'COP',
                                'USD' => 'USD',
                                'EUR' => 'EUR',
                            ])
                            ->default(fn() => Auth::user()->currency_default)
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
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, $state, $operation) {
                                // Si estamos creando, sincronizamos el saldo actual inmediatamente
                                if ($operation === 'create') {
                                    $set('current_balance', $state);
                                }
                            })
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

                        TextInput::make('current_balance')
                            ->label('Saldo actual')
                            ->prefix('$')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            // Definimos el valor por defecto para cuando carga el formulario
                            ->afterStateHydrated(function (TextInput $component, $state, ?Account $record, $operation) {
                                if ($operation === 'edit' && $record) {
                                    $component->state($record->current_balance);
                                }
                            })
                    ]),


                Section::make('Tarjeta de crédito')
                    ->description('Información financiera de la tarjeta de crédito')
                    ->icon(Heroicon::Banknotes)
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextInput::make('credit_limit')
                                    ->label('Límite de crédito')
                                    ->prefix('$')
                                    ->numeric(),

                                TextInput::make('credit_available')
                                    ->label('Disponible')
                                    ->prefix('$')
                                    ->numeric(),

                                TextInput::make('credit_interest_rate')
                                    ->label('Interés')
                                    ->prefix('$')
                                    ->numeric(),
                            ])
                            ->columns(3),

                        Grid::make()
                            ->schema([
                                DatePicker::make('credit_due_date')
                                    ->label('Fecha de vencimiento'),

                                DatePicker::make('credit_payment_date')
                                    ->label('Fecha de pago'),
                            ])
                            ->columns(2),
                    ])
                    ->hidden(fn(Get $get) => $get('type') == 'credit'),

            ]);
    }
}
