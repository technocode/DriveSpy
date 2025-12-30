<?php

namespace App\Filament\Resources\DriveItems\Schemas;

use App\Models\DriveItem;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DriveItemInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('googleAccount.id')
                    ->label('Google account'),
                TextEntry::make('monitoredFolder.id')
                    ->label('Monitored folder'),
                TextEntry::make('drive_file_id'),
                TextEntry::make('parent_drive_file_id')
                    ->placeholder('-'),
                TextEntry::make('name'),
                TextEntry::make('mime_type'),
                IconEntry::make('is_folder')
                    ->boolean(),
                TextEntry::make('path_cache')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('size_bytes')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('md5_checksum')
                    ->placeholder('-'),
                TextEntry::make('modified_time')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_time')
                    ->dateTime()
                    ->placeholder('-'),
                IconEntry::make('trashed')
                    ->boolean(),
                IconEntry::make('starred')
                    ->boolean(),
                IconEntry::make('owned_by_me')
                    ->boolean(),
                TextEntry::make('last_seen_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (DriveItem $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
