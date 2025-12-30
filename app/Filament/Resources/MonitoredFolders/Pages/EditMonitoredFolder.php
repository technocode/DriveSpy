<?php

namespace App\Filament\Resources\MonitoredFolders\Pages;

use App\Filament\Resources\MonitoredFolders\MonitoredFolderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMonitoredFolder extends EditRecord
{
    protected static string $resource = MonitoredFolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
