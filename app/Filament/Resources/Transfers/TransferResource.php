<?php

namespace App\Filament\Resources\Transfers;

use App\Filament\Resources\Transfers\Pages\CreateTransfer;
use App\Filament\Resources\Transfers\Pages\EditTransfer;
use App\Filament\Resources\Transfers\Pages\ListTransfers;
use App\Filament\Resources\Transfers\Pages\ViewTransfer;
use App\Filament\Resources\Transfers\Schemas\TransferForm;
use App\Filament\Resources\Transfers\Schemas\TransferInfolist;
use App\Filament\Resources\Transfers\Tables\TransfersTable;
use App\Models\Transfer;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TransferResource extends Resource
{
    protected static ?string $model = Transfer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowPath;

    protected static string | UnitEnum | null $navigationGroup = 'Finanzas';

    protected static ?string $navigationLabel = 'Transferencias';

    protected static ?string $pluralModelLabel = 'Transferencias';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::currentMonth()->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Transferencias del mes';
    }

    public static function form(Schema $schema): Schema
    {
        return TransferForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TransferInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransfersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransfers::route('/'),
            'create' => CreateTransfer::route('/create'),
            'view' => ViewTransfer::route('/{record}'),
            // 'edit' => EditTransfer::route('/{record}/edit'),
        ];
    }
}
