<?php

namespace App\Filament\Resources\SyncRuns\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SyncRunForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('google_account_id')
                    ->relationship('googleAccount', 'id')
                    ->required(),
                Select::make('monitored_folder_id')
                    ->relationship('monitoredFolder', 'id'),
                TextInput::make('run_type')
                    ->required(),
                TextInput::make('status')
                    ->required(),
                DateTimePicker::make('started_at')
                    ->required(),
                DateTimePicker::make('finished_at'),
                TextInput::make('items_scanned')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('changes_fetched')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('events_created')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('error_message')
                    ->columnSpanFull(),
                TextInput::make('meta_json'),
            ]);
    }
}
