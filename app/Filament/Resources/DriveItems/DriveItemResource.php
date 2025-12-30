<?php

namespace App\Filament\Resources\DriveItems;

use App\Filament\Resources\DriveItems\Pages\CreateDriveItem;
use App\Filament\Resources\DriveItems\Pages\EditDriveItem;
use App\Filament\Resources\DriveItems\Pages\ListDriveItems;
use App\Filament\Resources\DriveItems\Pages\ViewDriveItem;
use App\Filament\Resources\DriveItems\Schemas\DriveItemForm;
use App\Filament\Resources\DriveItems\Schemas\DriveItemInfolist;
use App\Filament\Resources\DriveItems\Tables\DriveItemsTable;
use App\Models\DriveItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DriveItemResource extends Resource
{
    protected static ?string $model = DriveItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static UnitEnum|string|null $navigationGroup = 'Drive Data';

    public static function form(Schema $schema): Schema
    {
        return DriveItemForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DriveItemInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DriveItemsTable::configure($table);
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
            'index' => ListDriveItems::route('/'),
            'create' => CreateDriveItem::route('/create'),
            'view' => ViewDriveItem::route('/{record}'),
            'edit' => EditDriveItem::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
