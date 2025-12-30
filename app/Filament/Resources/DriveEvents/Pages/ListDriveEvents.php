<?php

namespace App\Filament\Resources\DriveEvents\Pages;

use App\Filament\Resources\DriveEvents\DriveEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDriveEvents extends ListRecords
{
    protected static string $resource = DriveEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
