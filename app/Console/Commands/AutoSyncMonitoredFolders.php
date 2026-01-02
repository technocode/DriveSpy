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
        $accounts = MonitoredFolder::with('googleAccount')
            ->whereNotNull('last_indexed_at')
            ->get()
            ->pluck('googleAccount')
            ->unique('id');

        if ($accounts->isEmpty()) {
            $this->info('No Google accounts with indexed folders to sync.');

            return self::SUCCESS;
        }

        foreach ($accounts as $account) {
            SyncChangesJob::dispatch($account);
            $this->info("Queued sync for Google Account: {$account->email}");
        }

        $this->info("Total accounts queued: {$accounts->count()}");

        return self::SUCCESS;
    }
}
