<?php

namespace App\Filament\Resources\Transfers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TransferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('from_account_id')
                    ->label('Cuenta origen')
                    ->relationship('fromAccount', 'name')
                    ->live()
                    ->required(),

                Select::make('to_account_id')
                    ->label('Cuenta destino')
                    ->relationship('toAccount', 'name', function ($query, $get) {
                        $fromAccountId = $get('from_account_id');
                        return $query->when($fromAccountId, fn($q) => $q->where('id', '!=', $fromAccountId));
                    })
                    ->live()
                    ->required()
                    ->different('from_account_id')
                    ->validationMessages([
                        'different' => 'La cuenta de destino debe ser diferente a la de origen.',
                    ]),

                TextInput::make('amount')
                    ->numeric()
                    ->required(),

                DatePicker::make('transfer_date')
                    ->required()
                    ->native(false)
                    ->default(now()),

                Textarea::make('note')
                    ->columnSpanFull(),
            ]);
    }
}
