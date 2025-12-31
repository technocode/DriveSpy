<?php

use App\Jobs\FullSyncJob;
use App\Jobs\SyncChangesJob;
use App\Models\GoogleAccount;
use App\Models\MonitoredFolder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run incremental sync every 10 minutes for active accounts
Schedule::call(function () {
    GoogleAccount::where('status', 'active')
        ->whereNotNull('drive_start_page_token')
        ->each(function ($account) {
            SyncChangesJob::dispatch($account);
        });
})->everyTenMinutes()
    ->name('sync-drive-changes')
    ->withoutOverlapping();

// Run full sync daily at 2 AM for all active monitored folders
Schedule::call(function () {
    MonitoredFolder::where('status', 'active')
        ->whereNotNull('last_indexed_at')
        ->each(function ($folder) {
            FullSyncJob::dispatch($folder);
        });
})->dailyAt('02:00')
    ->name('full-sync-folders')
    ->withoutOverlapping();
