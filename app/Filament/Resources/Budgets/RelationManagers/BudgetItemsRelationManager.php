<?php

namespace App\Filament\Resources\Budgets\RelationManagers;

use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BudgetItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'budgetItems';

    protected static ?string $title = 'Items del Presupuesto';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('product_id')
                    ->label('Producto')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->createOptionForm(ProductForm::getFormSchema())
                    ->createOptionModalHeading('Crear Producto')
                    ->afterStateUpdated(function ($state, Set $set) {
                         if ($state) {
                             $product = Product::find($state);
                             if ($product) {
                                 $set('expected_amount', $product->expected_price ?? $product->price);
                                 $set('account_id', $product->default_account_id);
                             }
                         }
                    }),
                TextInput::make('expected_amount')
                    ->label('Valor Esperado')
                    ->numeric()
                    ->required()
                    ->prefix('$'),
                DatePicker::make('payment_date')
                    ->label('Fecha Esperada de Pago'),
                Select::make('account_id')
                    ->label('Cuenta')
                    ->relationship('account', 'name')
                    ->searchable()
                    ->preload(),
                Textarea::make('notes')
                    ->label('Notas')
                    ->maxLength(255)
                    ->rows(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('product.name')
                    ->label('Producto')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('payment_date')
                    ->label('Fecha Esperada de Pago')
                    ->date()
                    ->sortable(),
                TextColumn::make('expected_amount')
                    ->label('Valor Esperado')
                    ->money('COP') // Or dynamic currency
                    ->sortable(),
                TextColumn::make('actual_amount')
                    ->label('Valor a Pagar')
                    ->money('COP')
                    ->sortable(),
                TextColumn::make('pay_date')
                    ->label('Fecha de Pago')
                    ->date()
                    ->sortable(),
                TextColumn::make('account.name')
                    ->label('Cuenta'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
