<?php

namespace App\Filament\Resources\DriveItems\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DriveItemForm
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
                TextInput::make('parent_drive_file_id'),
                TextInput::make('name')
                    ->required(),
                TextInput::make('mime_type')
                    ->required(),
                Toggle::make('is_folder')
                    ->required(),
                Textarea::make('path_cache')
                    ->columnSpanFull(),
                TextInput::make('size_bytes')
                    ->numeric(),
                TextInput::make('md5_checksum'),
                DateTimePicker::make('modified_time'),
                DateTimePicker::make('created_time'),
                Toggle::make('trashed')
                    ->required(),
                Toggle::make('starred')
                    ->required(),
                Toggle::make('owned_by_me')
                    ->required(),
                TextInput::make('owners_json'),
                TextInput::make('permissions_json'),
                DateTimePicker::make('last_seen_at'),
            ]);
    }
}
