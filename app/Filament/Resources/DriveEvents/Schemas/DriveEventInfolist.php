<?php

namespace App\Filament\Resources\DriveEvents\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DriveEventInfolist
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
                TextEntry::make('event_type'),
                TextEntry::make('change_source'),
                TextEntry::make('occurred_at')
                    ->dateTime(),
                TextEntry::make('actor_email')
                    ->placeholder('-'),
                TextEntry::make('actor_name')
                    ->placeholder('-'),
                TextEntry::make('summary')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
