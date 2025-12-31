<?php

namespace App\Filament\Resources\MonitoredFolders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class MonitoredFolderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('google_account_id')
                    ->label('Google Account')
                    ->relationship('googleAccount', 'email')
                    ->required()
                    ->live(),
                ViewField::make('folder_picker')
                    ->label('')
                    ->view('filament.forms.components.google-drive-folder-picker-field')
                    ->visible(fn (Get $get) => $get('google_account_id')),
                TextInput::make('root_drive_file_id')
                    ->label('Folder ID')
                    ->required()
                    ->helperText('Select a folder using the picker above, or enter the folder ID manually'),
                TextInput::make('root_name')
                    ->label('Folder Name')
                    ->required(),
                Toggle::make('include_subfolders')
                    ->label('Monitor Subfolders')
                    ->helperText('Recursively monitor all subfolders within this folder')
                    ->default(true)
                    ->required(),
                TextInput::make('status')
                    ->default('active')
                    ->required()
                    ->hidden(),
                DateTimePicker::make('last_indexed_at')
                    ->disabled()
                    ->hidden(fn ($context) => $context === 'create'),
                DateTimePicker::make('last_changed_at')
                    ->disabled()
                    ->hidden(fn ($context) => $context === 'create'),
                Textarea::make('last_error')
                    ->disabled()
                    ->columnSpanFull()
                    ->hidden(fn ($context) => $context === 'create'),
            ]);
    }
}
