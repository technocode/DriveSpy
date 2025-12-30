<?php

namespace App\Filament\Resources\DriveEvents\Pages;

use App\Filament\Resources\DriveEvents\DriveEventResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDriveEvent extends ViewRecord
{
    protected static string $resource = DriveEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
