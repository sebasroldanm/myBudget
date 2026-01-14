<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre / Jerarquía')
                    ->description(
                        fn(Category $record) =>
                        $record->parent ? "Ruta: " . $record->parent->full_name : 'Categoría Raíz'
                    )
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'expense' => 'Gasto',
                        'income' => 'Ingreso',
                        default => $state,
                    })
                    ->colors([
                        'danger' => 'expense',
                        'success' => 'income',
                    ]),

                TextColumn::make('color')
                    ->label('Color')
                    ->html()
                    ->formatStateUsing(fn($state) => "<span style='color: {$state};'>{$state}</span>")
                    ->badge(),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
