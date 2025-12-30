<?php

namespace App\Filament\Resources\MonitoredFolders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MonitoredFolderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('google_account_id')
                    ->relationship('googleAccount', 'id')
                    ->required(),
                TextInput::make('root_drive_file_id')
                    ->required(),
                TextInput::make('root_name')
                    ->required(),
                Toggle::make('include_subfolders')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('active'),
                DateTimePicker::make('last_indexed_at'),
                DateTimePicker::make('last_changed_at'),
                Textarea::make('last_error')
                    ->columnSpanFull(),
            ]);
    }
}
