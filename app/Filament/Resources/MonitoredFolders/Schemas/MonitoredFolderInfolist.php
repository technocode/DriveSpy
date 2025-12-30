<?php

namespace App\Filament\Resources\MonitoredFolders\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MonitoredFolderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('googleAccount.id')
                    ->label('Google account'),
                TextEntry::make('root_drive_file_id'),
                TextEntry::make('root_name'),
                IconEntry::make('include_subfolders')
                    ->boolean(),
                TextEntry::make('status'),
                TextEntry::make('last_indexed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('last_changed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('last_error')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
