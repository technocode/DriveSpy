<?php

namespace App\Filament\Resources\DriveEvents\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DriveEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('google_account_id')
                    ->relationship('googleAccount', 'id')
                    ->required(),
                Select::make('monitored_folder_id')
                    ->relationship('monitoredFolder', 'id')
                    ->required(),
                TextInput::make('drive_file_id')
                    ->required(),
                TextInput::make('event_type')
                    ->required(),
                TextInput::make('change_source')
                    ->required()
                    ->default('changes_api'),
                DateTimePicker::make('occurred_at')
                    ->required(),
                TextInput::make('actor_email')
                    ->email(),
                TextInput::make('actor_name'),
                TextInput::make('before_json'),
                TextInput::make('after_json'),
                TextInput::make('summary'),
            ]);
    }
}
