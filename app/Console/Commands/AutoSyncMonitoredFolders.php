<?php

namespace App\Console\Commands;

use App\Jobs\SyncChangesJob;
use App\Models\MonitoredFolder;
use Illuminate\Console\Command;

class AutoSyncMonitoredFolders extends Command
{
    protected $signature = 'drivespy:auto-sync';

    protected $description = 'Automatically sync all monitored folders that have been indexed';

    public function handle(): int
    {
        $folders = MonitoredFolder::with('googleAccount')
            ->whereNotNull('last_indexed_at')
            ->get();

        if ($folders->isEmpty()) {
            $this->info('No monitored folders to sync.');

            return self::SUCCESS;
        }

        foreach ($folders as $folder) {
            SyncChangesJob::dispatch($folder->googleAccount, $folder);
            $this->info("Queued sync for: {$folder->root_name} ({$folder->googleAccount->email})");
        }

        $this->info("Total folders queued: {$folders->count()}");

        return self::SUCCESS;
    }
}
