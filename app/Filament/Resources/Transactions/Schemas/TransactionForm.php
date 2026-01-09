<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Models\Account;
use App\Models\Category;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Auth;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'income' => 'Ingreso',
                        'expense' => 'Gasto',
                    ])
                    ->required()
                    ->reactive(),

                Select::make('account_id')
                    ->label('Cuenta')
                    ->options(fn () =>
                        Account::where('user_id', Auth::user()->id)
                            ->where('is_active', true)
                            ->pluck('name', 'id')
                    )
                    ->live()
                    ->required(),

                Select::make('category_id')
                    ->label('Categoría')
                    ->options(fn (callable $get) =>
                        Category::where('user_id', Auth::user()->id)
                            ->where('type', $get('type'))
                            ->where('is_active', true)
                            ->pluck('name', 'id')
                    )
                    ->required(),

                TextInput::make('amount')
                    ->label('Monto')
                    ->prefix('$')
                    ->mask(RawJs::make('$money($input)'))
                    ->suffix(function (Get $get) {
                        $accountId = $get('account_id');
                        if (! $accountId) {
                            return Auth::user()->currency_default;
                        }
                        return Account::find($accountId)?->currency ?? Auth::user()->currency_default;
                    })
                    ->stripCharacters(',')
                    ->numeric()
                    ->required(),

                DatePicker::make('transaction_date')
                    ->label('Fecha')
                    ->default(now())
                    ->required(),

                Textarea::make('description')
                    ->label('Descripción')
                    ->columnSpanFull(),
            ]);
    }
}
