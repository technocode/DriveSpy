<?php

namespace App\Filament\Resources\GoogleAccounts\Pages;

use App\Filament\Resources\GoogleAccounts\GoogleAccountResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditGoogleAccount extends EditRecord
{
    protected static string $resource = GoogleAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
