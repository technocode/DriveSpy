<?php

namespace App\Filament\Resources\GoogleAccounts\Pages;

use App\Filament\Resources\GoogleAccounts\GoogleAccountResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListGoogleAccounts extends ListRecords
{
    protected static string $resource = GoogleAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('connect_google')
                ->label('Connect Google Account')
                ->icon(Heroicon::OutlinedPlus)
                ->color('primary')
                ->url(route('google.oauth.redirect')),
            CreateAction::make()
                ->label('Manual Entry')
                ->visible(false),
        ];
    }
}
