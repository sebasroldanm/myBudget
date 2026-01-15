<?php

namespace App\Filament\Resources\Transactions\RelationManagers;

use App\Traits\NumberFormatterTrait;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LogsRelationManager extends RelationManager
{
    use NumberFormatterTrait;

    protected static string $relationship = 'logs';


    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('event')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([
                // --- SECCIÓN 1: AUDITORÍA ---
                Section::make('Detalles de la Auditoría')
                    ->icon('heroicon-o-finger-print')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('event')
                                ->label('Acción Realizada')
                                ->badge()
                                ->color(fn(string $state): string => match ($state) {
                                    'created' => 'success',
                                    'updated' => 'warning',
                                    'deleted' => 'danger',
                                    'restored'  => 'info',
                                    default   => 'gray',
                                })
                                ->formatStateUsing(fn($state): string => match ($state) {
                                    'created' => 'Creado',
                                    'updated' => 'Actualizado',
                                    'deleted' => 'Eliminado',
                                    'restored'  => 'Restaurado',
                                    default   => $state,
                                }),
                            TextEntry::make('event_at')
                                ->label('Fecha y Hora')
                                ->dateTime('d/m/Y H:i:s'),
                            TextEntry::make('user.name')
                                ->label('Responsable')
                                ->placeholder('Sistema'),
                            TextEntry::make('ip_address')
                                ->label('IP Origen'),
                        ]),
                    ]),

                // --- SECCIÓN 2: TRANSACCIÓN Y PRODUCTO ---
                Section::make('Detalle de Transacción y Producto')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->columnSpan(2)
                    ->schema([
                        Grid::make(2)->schema([
                            // Datos base
                            TextEntry::make('amount')
                                ->label('Monto (Base)')
                                ->state(fn($record) => $this->formatCurrency((float)$record->amount, $record->account_currency ?? 'COP'))
                                ->weight(FontWeight::Bold)
                                ->size(TextSize::Large),

                            TextEntry::make('currency_amount')
                                ->label('Monto (Divisa)')
                                ->state(fn($record) => $record->currency_amount
                                    ? $this->formatCurrency((float)$record->currency_amount, $record->product_currency ?? 'USD')
                                    : 'N/A'),

                            TextEntry::make('transaction_date')->label('Fecha Operación')->date('d/m/Y'),

                            TextEntry::make('is_locked')
                                ->label('Estado')
                                ->badge()
                                ->state(fn($record) => $record->is_locked ? 'Bloqueada' : 'Abierta')
                                ->color(fn($state) => $state === 'Bloqueada' ? 'danger' : 'success'),

                            // DATOS DEL PRODUCTO (Snapshot)
                            Section::make('Producto/Servicio Asociado')
                                ->compact()
                                ->schema([
                                    Grid::make(3)->schema([
                                        TextEntry::make('product_name')->label('Nombre')->placeholder('N/A'),
                                        TextEntry::make('product_vendor')->label('Proveedor')->placeholder('N/A'),
                                        TextEntry::make('product_price_strategy')->label('Estrategia')->badge(),

                                        TextEntry::make('product_price')
                                            ->label('Precio Pactado')
                                            ->state(fn($record) => $this->formatCurrency((float)$record->product_price, $record->product_currency ?? 'USD')),

                                        TextEntry::make('product_is_recurring')
                                            ->label('Recurrente')
                                            ->formatStateUsing(fn($state) => $state ? 'Sí' : 'No'),
                                    ]),
                                ])->visible(fn($record) => $record->product_id !== null),

                            TextEntry::make('transaction_description')
                                ->label('Descripción')
                                ->columnSpanFull(),
                        ]),
                    ]),

                // --- SECCIÓN 3: IMPACTO FINANCIERO ---
                Section::make('Resumen de Saldos')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('account_name')
                            ->label('Cuenta')
                            ->weight(FontWeight::Bold),

                        TextEntry::make('account_type')
                            ->label('Tipo')
                            ->formatStateUsing(fn($state) => match ($state) {
                                'cash' => 'Efectivo',
                                'bank' => 'Banco',
                                'wallet' => 'Billetera',
                                'credit' => 'Crédito',
                                'investment' => 'Inversión',
                                default => $state,
                            }),

                        // Balances en Moneda Local
                        Section::make('Moneda Local (' . ($record->account_currency ?? 'COP') . ')')
                            ->compact()
                            ->schema([
                                TextEntry::make('balance_before')
                                    ->label('Saldo Antes')
                                    ->state(fn($record) => $this->formatCurrency((float)$record->balance_before, $record->account_currency ?? 'COP')),

                                TextEntry::make('balance_after')
                                    ->label('Saldo Después')
                                    ->weight(FontWeight::Bold)
                                    ->state(fn($record) => $this->formatCurrency((float)$record->balance_after, $record->account_currency ?? 'COP'))
                                    ->color(fn($record) => $record->balance_after >= $record->balance_before ? 'success' : 'danger'),
                            ]),

                        // Balances en Divisa (Si existen)
                        Section::make('Balance en Divisa')
                            ->compact()
                            ->visible(fn($record) => $record->currency_balance_after !== null)
                            ->schema([
                                TextEntry::make('currency_balance_before')
                                    ->label('Antes')
                                    ->state(fn($record) => $this->formatCurrency((float)$record->currency_balance_before, $record->product_currency ?? 'USD')),

                                TextEntry::make('currency_balance_after')
                                    ->label('Después')
                                    ->state(fn($record) => $this->formatCurrency((float)$record->currency_balance_after, $record->product_currency ?? 'USD')),
                            ]),
                    ]),

                // --- SECCIÓN 4: METADATOS TÉCNICOS ---
                Section::make('Información Técnica')
                    ->collapsed()
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('user_agent')
                            ->label('Cliente')
                            ->fontFamily('mono')
                            ->size(TextSize::ExtraSmall),
                        TextEntry::make('meta')
                            ->label('Meta JSON'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('event')
            ->columns([
                TextColumn::make('event')
                    ->badge()
                    ->label('Evento')
                    ->formatStateUsing(fn($state): string => match ($state) {
                        'created' => 'Creado',
                        'updated' => 'Actualizado',
                        'deleted' => 'Eliminado',
                        'restored'  => 'Restaurado',
                        default   => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        'restored' => 'info',
                        default   => 'gray',
                    }),
                TextColumn::make('event_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Usuario'),
                TextColumn::make('balance_after')
                    ->label('Saldo Resultante')
                    ->money('COP'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                ]),
            ])
            ->defaultSort('event_at', 'desc');
    }
}
