<?php

namespace App\Filament\Resources\Accounts\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
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
                            ->placeholder('Ej. Banco de Bogotá')
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
                            ->live()
                            ->required(),

                        TextInput::make('initial_balance')
                            ->label('Saldo inicial')
                            ->prefix('$')
                            ->suffix(fn(Get $get) => $get('currency'))
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->default(0)
                            ->placeholder('0.00')
                            ->trim()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, $state, $operation) {
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
                            ->disabled(function ($record) {
                                if (! $record) {
                                    return false;
                                }
                                return $record->transactions()->count() > 0;
                            })
                            ->readOnly(fn(Get $get) => $get('type') === 'credit_card')
                            ->required(),

                        TextInput::make('current_balance')
                            ->label('Saldo actual')
                            ->prefix('$')
                            ->suffix(fn(Get $get) => $get('currency'))
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->placeholder('0.00')
                            ->readOnly()
                    ]),


                Section::make('Tarjeta de crédito')
                    ->description('Información financiera de la tarjeta de crédito')
                    ->icon(Heroicon::Banknotes)
                    ->visible(fn(Get $get) => $get('type') === 'credit_card')
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextInput::make('credit_limit')
                                    ->label('Cupo total de tarjeta')
                                    ->prefix('$')
                                    ->suffix(fn(Get $get) => $get('currency'))
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->live(onBlur: true)
                                    ->placeholder('0.00'),

                                TextInput::make('credit_available')
                                    ->label('Limite de cupo')
                                    ->prefix('$')
                                    ->suffix(fn(Get $get) => $get('currency'))
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->live(onBlur: true)
                                    ->placeholder(fn(Get $get) => ($get('credit_limit') ? 'Máximo: ' . $get('credit_limit') : '0'))
                                    ->afterStateUpdated(function (Set $set, Get $get, $record, $state, $operation) {
                                        $value_state = (float) str_replace(',', '', $state);
                                        $value_current_balance = (float) str_replace(',', '', $record->current_balance);
                                        $value_credit_limit = (float) str_replace(',', '', $record->credit_limit);
                                        if ($operation === 'create') {
                                            $set('current_balance', $state);
                                        } else {
                                            if ($value_current_balance === $value_state) {
                                                $set('current_balance', $state);
                                            } else {
                                                $value = $value_credit_limit - $value_state;
                                                if ($value > 0) {
                                                    $set('current_balance', $state);
                                                } else {
                                                    $set('credit_available', $get('current_balance'));
                                                    Notification::make()
                                                        ->title('Error')
                                                        ->body('El saldo actual no puede ser mayor al cupo disponible de la tarjeta.')
                                                        ->danger()
                                                        ->send();
                                                }
                                            }
                                        }
                                    }),

                                TextInput::make('credit_interest_rate')
                                    ->label('Interés E.A.')
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%')
                                    ->numeric(),
                            ])
                            ->columns(3),

                        Grid::make()
                            ->schema([
                                DatePicker::make('credit_due_date')
                                    ->label('Día de vencimiento')
                                    ->native(false)
                                    ->displayFormat('d')
                                    ->minDate(now()->startOfMonth())
                                    ->maxDate(now()->endOfMonth())
                                    ->closeOnDateSelection(),

                                DatePicker::make('credit_payment_date')
                                    ->label('Día de pago')
                                    ->native(false)
                                    ->displayFormat('d')
                                    ->minDate(now()->startOfMonth())
                                    ->maxDate(now()->endOfMonth())
                                    ->closeOnDateSelection(),
                            ])
                            ->columns(2),
                    ])
                    ->hidden(fn(Get $get) => $get('type') == 'credit'),

            ]);
    }
}
