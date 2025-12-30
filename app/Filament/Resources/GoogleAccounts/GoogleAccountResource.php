<?php

namespace App\Filament\Resources\GoogleAccounts;

use App\Filament\Resources\GoogleAccounts\Pages\CreateGoogleAccount;
use App\Filament\Resources\GoogleAccounts\Pages\EditGoogleAccount;
use App\Filament\Resources\GoogleAccounts\Pages\ListGoogleAccounts;
use App\Filament\Resources\GoogleAccounts\Pages\ViewGoogleAccount;
use App\Filament\Resources\GoogleAccounts\Schemas\GoogleAccountForm;
use App\Filament\Resources\GoogleAccounts\Schemas\GoogleAccountInfolist;
use App\Filament\Resources\GoogleAccounts\Tables\GoogleAccountsTable;
use App\Models\GoogleAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class GoogleAccountResource extends Resource
{
    protected static ?string $model = GoogleAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static UnitEnum|string|null $navigationGroup = 'Configuration';

    public static function form(Schema $schema): Schema
    {
        return GoogleAccountForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GoogleAccountInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GoogleAccountsTable::configure($table);
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
            'index' => ListGoogleAccounts::route('/'),
            'create' => CreateGoogleAccount::route('/create'),
            'view' => ViewGoogleAccount::route('/{record}'),
            'edit' => EditGoogleAccount::route('/{record}/edit'),
        ];
    }
}
