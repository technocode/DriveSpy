<?php

namespace App\Filament\Resources\DriveEvents;

use App\Filament\Resources\DriveEvents\Pages\CreateDriveEvent;
use App\Filament\Resources\DriveEvents\Pages\EditDriveEvent;
use App\Filament\Resources\DriveEvents\Pages\ListDriveEvents;
use App\Filament\Resources\DriveEvents\Pages\ViewDriveEvent;
use App\Filament\Resources\DriveEvents\Schemas\DriveEventForm;
use App\Filament\Resources\DriveEvents\Schemas\DriveEventInfolist;
use App\Filament\Resources\DriveEvents\Tables\DriveEventsTable;
use App\Models\DriveEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DriveEventResource extends Resource
{
    protected static ?string $model = DriveEvent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static UnitEnum|string|null $navigationGroup = 'Drive Data';

    public static function form(Schema $schema): Schema
    {
        return DriveEventForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DriveEventInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DriveEventsTable::configure($table);
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
            'index' => ListDriveEvents::route('/'),
            'create' => CreateDriveEvent::route('/create'),
            'view' => ViewDriveEvent::route('/{record}'),
            'edit' => EditDriveEvent::route('/{record}/edit'),
        ];
    }
}
