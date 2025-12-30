<?php

namespace App\Filament\Resources\SyncRuns\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SyncRunInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('googleAccount.id')
                    ->label('Google account'),
                TextEntry::make('monitoredFolder.id')
                    ->label('Monitored folder')
                    ->placeholder('-'),
                TextEntry::make('run_type'),
                TextEntry::make('status'),
                TextEntry::make('started_at')
                    ->dateTime(),
                TextEntry::make('finished_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('items_scanned')
                    ->numeric(),
                TextEntry::make('changes_fetched')
                    ->numeric(),
                TextEntry::make('events_created')
                    ->numeric(),
                TextEntry::make('error_message')
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
