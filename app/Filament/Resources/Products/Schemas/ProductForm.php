<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Account;
use App\Services\ExchangeService;
use App\Traits\NumberFormatterTrait;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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

class ProductForm
{
    use NumberFormatterTrait;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General')
                    ->description('Información general del producto')
                    ->icon(Heroicon::DocumentText)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('vendor')
                                    ->label('Proveedor/Empresa')
                                    ->maxLength(255),

                                Select::make('default_account_id')
                                    ->label('Cuenta por defecto')
                                    ->relationship('defaultAccount', 'name')
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->name . ' - ' . $record->formatted_balance)
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                        if ($state) {
                                            $account = Account::find($state);
                                            if ($account) {
                                                $set('currency', $account->currency);
                                            }
                                        } else {
                                            $set('currency', Auth::user()->currency_default);
                                        }
                                    })
                                    ->preload(),
                            ]),

                        Select::make('category_id')
                            ->label('Categoría')
                            ->relationship(
                                name: 'category', 
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->active()->expenses(),
                            )
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->full_name)
                            ->searchable()
                            ->preload()
                            ->required(),

                        Grid::make(12)
                            ->schema([
                                TextInput::make('price')
                                    ->label('Precio')
                                    ->prefix('$')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->dehydrateStateUsing(fn($state) => $state ? (float) str_replace(',', '', $state) : 0)
                                    ->live(onBlur: true)
                                    ->required()
                                    ->suffix(function (Get $get) {
                                        $currency = $get('currency');
                                        $userCurrency = Auth::user()->currency_default;
                                        $priceRaw = $get('price');
                                        $exchangeService = app(ExchangeService::class);
                                        if ($currency !== $userCurrency) {
                                            return $exchangeService->convert($currency, $userCurrency, $priceRaw, 'withSymbol');
                                        }
                                        return $userCurrency;
                                    })
                                    ->columnSpan(8),

                                Select::make('currency')
                                    ->label('Moneda')
                                    ->options([
                                        'COP' => 'COP',
                                        'USD' => 'USD',
                                        'EUR' => 'EUR',
                                    ])
                                    ->disabled(fn(Get $get) => filled($get('default_account_id')))
                                    ->dehydrated(true)
                                    ->live()
                                    ->default(fn() => Auth::user()->currency_default)
                                    ->required()
                                    ->columnSpan(4),
                            ]),
                        Toggle::make('is_active')
                            ->label('Activo')
                            ->inline(false)
                            ->onColor('success')
                            ->offColor('danger')
                            ->onIcon(Heroicon::CheckCircle)
                            ->offIcon(Heroicon::XCircle)
                            ->default(true),
                    ]),

                Section::make('Recurrencia')
                    ->description('Información de recurrencia')
                    ->icon(Heroicon::Calendar)
                    ->schema([
                        Toggle::make('is_recurring')
                            ->label('Es recurrente')
                            ->onIcon(Heroicon::ArrowPath)
                            ->onColor('success')
                            ->inline(false)
                            ->live(),

                        Grid::make(12)
                            ->schema([
                                Select::make('price_strategy')
                                    ->label('Estrategia de precio')
                                    ->options([
                                        'fixed' => 'Fijo',
                                        'variable' => 'Variable',
                                        'estimate' => 'Estimado',
                                    ])
                                    ->required()
                                    ->live()
                                    ->visible(fn($get) => $get('is_recurring'))
                                    ->columnSpan(4),

                                TextInput::make('expected_price')
                                    ->label('Precio esperado')
                                    ->prefix('$')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->dehydrateStateUsing(fn($state) => $state ? (float) str_replace(',', '', $state) : 0)
                                    ->live(onBlur: true)
                                    ->required()
                                    ->suffix(function (Get $get) {
                                        $currency = $get('currency');
                                        $userCurrency = Auth::user()->currency_default;
                                        $expectedPriceRaw = $get('expected_price');
                                        $exchangeService = app(ExchangeService::class);
                                        if ($currency !== $userCurrency) {
                                            return $exchangeService->convert($currency, $userCurrency, $expectedPriceRaw, 'withSymbol');
                                        }
                                        return $userCurrency;
                                    })
                                    ->visible(fn($get) => $get('is_recurring') && in_array($get('price_strategy'), ['variable', 'estimate']))
                                    ->columnSpan(8),
                            ]),

                        Grid::make(2)
                            ->schema([
                                DatePicker::make('payment_date')
                                    ->label('Fecha de pago')
                                    ->visible(fn($get) => $get('is_recurring')),

                                Select::make('periodicity')
                                    ->label('Periodicidad')
                                    ->options([
                                        'daily' => 'Diario',
                                        'weekly' => 'Semanal',
                                        'monthly' => 'Mensual',
                                        'yearly' => 'Anual',
                                    ])
                                    ->visible(fn($get) => $get('is_recurring')),

                                DatePicker::make('start_date')
                                    ->label('Fecha de inicio')
                                    ->visible(fn($get) => $get('is_recurring')),

                                DatePicker::make('end_date')
                                    ->label('Fecha de fin')
                                    ->visible(fn($get) => $get('is_recurring')),

                            ]),
                    ]),

                Section::make('Información adicional')
                    ->schema([
                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
