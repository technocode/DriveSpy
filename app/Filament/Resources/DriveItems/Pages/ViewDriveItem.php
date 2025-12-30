<?php

namespace App\Filament\Resources\DriveItems\Pages;

use App\Filament\Resources\DriveItems\DriveItemResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDriveItem extends ViewRecord
{
    protected static string $resource = DriveItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
