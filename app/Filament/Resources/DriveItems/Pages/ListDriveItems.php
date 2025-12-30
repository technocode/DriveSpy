<?php

namespace App\Filament\Resources\DriveItems\Pages;

use App\Filament\Resources\DriveItems\DriveItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDriveItems extends ListRecords
{
    protected static string $resource = DriveItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
