<?php

use App\Jobs\FullSyncJob;
use App\Models\MonitoredFolder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run incremental sync every minute for monitored folders
Schedule::command('drivespy:auto-sync')
    ->everyMinute()
    ->name('sync-drive-changes')
    ->withoutOverlapping();

// Full sync disabled - only using incremental sync
// Schedule::call(function () {
//     MonitoredFolder::where('status', 'active')
//         ->whereNotNull('last_indexed_at')
//         ->each(function ($folder) {
//             FullSyncJob::dispatch($folder);
//         });
// })->dailyAt('02:00')
//     ->name('full-sync-folders')
//     ->withoutOverlapping();
