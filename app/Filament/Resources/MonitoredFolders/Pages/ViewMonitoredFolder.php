<?php

namespace App\Filament\Resources\MonitoredFolders\Pages;

use App\Filament\Resources\MonitoredFolders\MonitoredFolderResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMonitoredFolder extends ViewRecord
{
    protected static string $resource = MonitoredFolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
