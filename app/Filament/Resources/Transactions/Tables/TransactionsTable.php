<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Services\TransactionService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use App\Traits\NumberFormatterTrait;

class TransactionsTable
{
    use NumberFormatterTrait;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_date')
                    ->label('Fecha')
                    ->sortable()
                    ->date(),

                TextColumn::make('account.name')
                    ->label('Cuenta'),

                TextColumn::make('description_type')
                    ->label('Categoría / Transferencia')
                    ->state(function ($record) {
                        if ($record->transfer_id) {
                            $from = $record->transfer->fromAccount->name ?? '?';
                            $to = $record->transfer->toAccount->name ?? '?';
                            return "🔄 Transferencia: {$from} ➔ {$to}";
                        }
                        if ($record->category) {
                            return "📁 " . $record->category->name;
                        }
                        return $record->description ?? 'Sin concepto';
                    })
                    ->searchable(['description'])
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Monto')
                    ->formatStateUsing(function ($record, $state) {
                        return (new self())->formatCurrency(
                            (float) $state, 
                            $record->account->currency
                        );
                    }),

                TextColumn::make('type')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'income' => 'Ingreso',
                        'expense' => 'Gasto',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'income',
                        'danger' => 'expense',
                    ]),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->hidden(fn ($record) => $record->is_locked),
                DeleteAction::make()
                    ->hidden(fn ($record) => $record->is_locked)
                    ->before(function ($record) {
                        app(TransactionService::class)->deleteTransaction($record);
                    }),
                RestoreAction::make()
                    ->before(function ($record) {
                        app(TransactionService::class)->restoreTransaction($record);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make()
                    //     ->action(function (Collection $records) {
                    //         $records->each(fn ($record) => app(TransactionService::class)->deleteTransaction($record));
                    //     }),
                ]),
            ]);
    }
}
