<?php

namespace App\Filament\Resources\GoogleAccounts\Pages;

use App\Filament\Resources\GoogleAccounts\GoogleAccountResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGoogleAccount extends ViewRecord
{
    protected static string $resource = GoogleAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
