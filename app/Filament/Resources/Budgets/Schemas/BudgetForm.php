<?php

namespace App\Filament\Resources\Budgets\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class BudgetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->columns([
                        'default' => 1,
                        'lg' => 3,
                    ])
                    ->components([
                        TextInput::make('name')
                            ->label('Nombre del presupuesto')
                            ->placeholder('Ej: Presupuesto ' . now()->format('Y-m'))
                            ->required()
                            ->maxLength(100),

                        Select::make('currency')
                            ->label('Moneda')
                            ->options([
                                'COP' => 'COP',
                                'USD' => 'USD',
                                'EUR' => 'EUR',
                            ])
                            ->required(),

                        TextInput::make('budget_amount')
                            ->label('Monto del presupuesto')
                            ->prefix('$')
                            ->required()
                            ->numeric()
                            ->minValue(0),

                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'draft' => 'Borrador',
                                'active' => 'Activo',
                                'inactive' => 'Inactivo',
                                'locked' => 'Bloqueado',
                            ])
                            ->default('draft')
                            ->required(),

                    ]),

                Grid::make()
                    ->columns([
                        'default' => 1,
                        'lg' => 2,
                    ])
                    ->components([
                        DatePicker::make('period_start')
                            ->label('Fecha Inicio')
                            ->default(now()->startOfMonth())
                            ->native(false)
                            ->required(),

                        DatePicker::make('period_end')
                            ->label('Fecha Fin')
                            ->default(now()->endOfMonth())
                            ->native(false)
                            ->required()
                            ->after('period_start'),

                    ]),

            ]);
    }
}
