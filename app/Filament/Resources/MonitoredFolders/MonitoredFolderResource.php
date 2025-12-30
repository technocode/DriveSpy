<?php

namespace App\Filament\Resources\MonitoredFolders;

use App\Filament\Resources\MonitoredFolders\Pages\CreateMonitoredFolder;
use App\Filament\Resources\MonitoredFolders\Pages\EditMonitoredFolder;
use App\Filament\Resources\MonitoredFolders\Pages\ListMonitoredFolders;
use App\Filament\Resources\MonitoredFolders\Pages\ViewMonitoredFolder;
use App\Filament\Resources\MonitoredFolders\Schemas\MonitoredFolderForm;
use App\Filament\Resources\MonitoredFolders\Schemas\MonitoredFolderInfolist;
use App\Filament\Resources\MonitoredFolders\Tables\MonitoredFoldersTable;
use App\Models\MonitoredFolder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MonitoredFolderResource extends Resource
{
    protected static ?string $model = MonitoredFolder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static UnitEnum|string|null $navigationGroup = 'Configuration';

    public static function form(Schema $schema): Schema
    {
        return MonitoredFolderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MonitoredFolderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MonitoredFoldersTable::configure($table);
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
            'index' => ListMonitoredFolders::route('/'),
            'create' => CreateMonitoredFolder::route('/create'),
            'view' => ViewMonitoredFolder::route('/{record}'),
            'edit' => EditMonitoredFolder::route('/{record}/edit'),
        ];
    }
}
