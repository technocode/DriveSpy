<?php

namespace App\Filament\Resources\GoogleAccounts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class GoogleAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name'),
                TextInput::make('google_user_id')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('display_name'),
                TextInput::make('avatar_url')
                    ->url(),
                DateTimePicker::make('token_expires_at'),
                Textarea::make('scopes')
                    ->columnSpanFull(),
                DateTimePicker::make('last_synced_at'),
                TextInput::make('status')
                    ->required()
                    ->default('active'),
                Textarea::make('last_error')
                    ->columnSpanFull(),
            ]);
    }
}
