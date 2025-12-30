<?php

namespace App\Jobs;

use App\Models\DriveItem;
use App\Models\MonitoredFolder;
use App\Models\SyncRun;
use App\Services\GoogleDriveService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ReindexFolderJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public MonitoredFolder $monitoredFolder
    ) {}

    public function handle(): void
    {
        $syncRun = SyncRun::create([
            'google_account_id' => $this->monitoredFolder->google_account_id,
            'monitored_folder_id' => $this->monitoredFolder->id,
            'run_type' => 'manual_reindex',
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $driveService = new GoogleDriveService($this->monitoredFolder->googleAccount);

            $files = $driveService->listAllFilesRecursively($this->monitoredFolder->root_drive_file_id);

            $itemsScanned = 0;
            $seenFileIds = [];

            foreach ($files as $file) {
                DriveItem::updateOrCreate(
                    [
                        'google_account_id' => $this->monitoredFolder->google_account_id,
                        'drive_file_id' => $file->getId(),
                    ],
                    [
                        'monitored_folder_id' => $this->monitoredFolder->id,
                        'parent_drive_file_id' => $file->getParents()[0] ?? null,
                        'name' => $file->getName(),
                        'mime_type' => $file->getMimeType(),
                        'is_folder' => $driveService->isFolder($file),
                        'size_bytes' => $file->getSize(),
                        'md5_checksum' => $file->getMd5Checksum(),
                        'modified_time' => $file->getModifiedTime(),
                        'created_time' => $file->getCreatedTime(),
                        'trashed' => $file->getTrashed() ?? false,
                        'starred' => $file->getStarred() ?? false,
                        'owned_by_me' => $file->getOwnedByMe() ?? false,
                        'owners_json' => $file->getOwners(),
                        'last_seen_at' => now(),
                    ]
                );

                $seenFileIds[] = $file->getId();
                $itemsScanned++;
            }

            DriveItem::where('monitored_folder_id', $this->monitoredFolder->id)
                ->whereNotIn('drive_file_id', $seenFileIds)
                ->where('last_seen_at', '<', now()->subHours(1))
                ->update(['trashed' => true]);

            $this->monitoredFolder->update([
                'last_indexed_at' => now(),
                'status' => 'active',
                'last_error' => null,
            ]);

            $syncRun->update([
                'status' => 'success',
                'finished_at' => now(),
                'items_scanned' => $itemsScanned,
            ]);

        } catch (Throwable $e) {
            $syncRun->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            $this->monitoredFolder->update([
                'status' => 'error',
                'last_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
