<?php

namespace App\Filament\Resources\MonitoredFolders\Pages;

use App\Filament\Resources\MonitoredFolders\MonitoredFolderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMonitoredFolders extends ListRecords
{
    protected static string $resource = MonitoredFolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
