<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Models\Account;
use App\Models\BudgetItem;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TransactionForm
{

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'default' => 1,
                'lg' => 3,
            ])
            ->components([
                Section::make('Detalles de la transacción')
                    ->description('Detalles de la transacción')
                    ->icon(Heroicon::ArrowRightStartOnRectangle)
                    ->schema([
                        Grid::make(12)
                            ->schema([
                                Select::make('type')
                                    ->label('Tipo')
                                    ->options([
                                        'income' => 'Ingreso',
                                        'expense' => 'Gasto',
                                    ])
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $set('product_id', null);
                                        $set('account_id', null);
                                        $set('amount', null);
                                        $set('category_id', null);
                                        $set('description', null);
                                    })
                                    ->columnSpan(4),

                                Select::make('budget_item_id')
                                    ->label('Ítem de presupuesto')
                                    ->relationship(
                                        name: 'budgetItem',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn(Builder $query, Get $get) => $query
                                            ->where('is_paid', false)
                                            ->whereHas('budget', function (Builder $query) {
                                                $query->where('status', 'active');
                                            })
                                    )
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->product->name . ' - ' . $record->product->formatted_price)
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                        if ($state) {
                                            $budgetItem = BudgetItem::find($state);
                                            if ($budgetItem) {
                                                if ($budgetItem->account_id) {
                                                    $set('account_id', $budgetItem->account_id);
                                                    $set('category_id', $budgetItem->product->category_id);
                                                    $set('amount', $budgetItem->product->expected_price);
                                                    $set('transaction_date', $budgetItem->payment_date);
                                                }
                                            }
                                        }
                                    })
                                    ->disabled(fn(Get $get) => $get('type') !== 'expense')
                                    ->columnSpan(8),
                            ]),

                        Grid::make(12)
                            ->schema([
                                Select::make('account_id')
                                    ->label('Cuenta')
                                    ->relationship('account', 'name')
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->name . ' - ' . $record->formatted_balance)
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->required()
                                    ->columnSpan(5),

                                Select::make('category_id')
                                    ->label('Categoría')
                                    ->relationship(
                                        name: 'category',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn(Builder $query, Get $get) => $query
                                            ->where('type', $get('type'))
                                            ->active()
                                    )
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->full_name)
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(7),
                            ]),



                        TextInput::make('amount')
                            ->label('Monto')
                            ->prefix('$')
                            ->mask(RawJs::make('$money($input)'))
                            ->dehydrateStateUsing(fn($state) => $state ? (float) str_replace(',', '', $state) : 0)
                            ->live(onBlur: true)
                            ->suffix(function (Get $get) {
                                $accountId = $get('account_id');
                                if (! $accountId) {
                                    return Auth::user()->currency_default;
                                }
                                return Account::find($accountId)?->currency ?? Auth::user()->currency_default;
                            })
                            ->required(),

                        DatePicker::make('transaction_date')
                            ->label('Fecha')
                            ->default(now())
                            ->required()
                            ->native(false)
                            ->live(),

                        Textarea::make('description')
                            ->label('Descripción')
                            ->columnSpanFull(),
                    ])
                    ->columnSpan([
                        'lg' => 2,
                    ]),

                Section::make('Detalles')
                    ->description('Detalles de la transacción')
                    ->icon(Heroicon::InformationCircle)
                    ->schema([
                        Section::make('Transacción')
                            ->schema([
                                TextEntry::make('transaction_account')
                                    ->label('Cuenta')
                                    ->inlineLabel()
                                    ->badge()
                                    ->color('info')
                                    ->state(function (Get $get) {
                                        $accountId = $get('account_id');
                                        if (! $accountId) {
                                            return 'Cuenta no seleccionada';
                                        }
                                        $account = Account::find($accountId);
                                        if (! $account) {
                                            return 'Cuenta no encontrada';
                                        }
                                        return $account->name;
                                    }),
                                TextEntry::make('transaction_amount')
                                    ->label('Valor')
                                    ->inlineLabel()
                                    ->badge()
                                    ->color(fn(Get $get) => $get('type') === 'expense' ? 'danger' : 'success')
                                    ->state(function (Get $get) {
                                        $amount = (float) str_replace(',', '', $get('amount'));
                                        $accountId = $get('account_id');
                                        if (! $accountId) {
                                            $currency = Auth::user()->currency_default;
                                        } else {
                                            $account = Account::find($accountId);
                                            if (! $account) {
                                                return 'Cuenta no encontrada';
                                            }
                                            $currency = $account->currency;
                                        }
                                        if (! $amount) {
                                            return 'Monto no seleccionado';
                                        }
                                        return ($get('type') === 'expense' ? '-' : '+') . \App\Traits\NumberFormatterTrait::formatCurrency($amount, $currency);
                                    }),
                                TextEntry::make('transaction_final_amount')
                                    ->label('Saldo final')
                                    ->inlineLabel()
                                    ->badge()
                                    ->color('success')
                                    ->state(function (Get $get) {
                                        $amount = (float) str_replace(',', '', $get('amount'));
                                        if (! $amount) {
                                            return 'Monto no seleccionado';
                                        }
                                        $accountId = $get('account_id');
                                        if (! $accountId) {
                                            $currency = Auth::user()->currency_default;
                                        } else {
                                            $account = Account::find($accountId);
                                            if (! $account) {
                                                return 'Cuenta no encontrada';
                                            }
                                            $currency = $account->currency;
                                        }
                                        if ($get('type') === 'expense') {
                                            return \App\Traits\NumberFormatterTrait::formatCurrency($account->current_balance - $amount, $currency);
                                        } else {
                                            return \App\Traits\NumberFormatterTrait::formatCurrency($account->current_balance + $amount, $currency);
                                        }
                                    }),
                            ]),
                    ])
                    ->columnSpan([
                        'lg' => 1,
                    ])
                    ->secondary(),
            ]);
    }
}
