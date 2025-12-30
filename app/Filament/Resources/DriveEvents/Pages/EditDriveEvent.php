<?php

namespace App\Filament\Resources\DriveEvents\Pages;

use App\Filament\Resources\DriveEvents\DriveEventResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDriveEvent extends EditRecord
{
    protected static string $resource = DriveEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
