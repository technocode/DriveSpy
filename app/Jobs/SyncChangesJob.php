<?php

namespace App\Jobs;

use App\Models\DriveEvent;
use App\Models\DriveItem;
use App\Models\GoogleAccount;
use App\Models\SyncRun;
use App\Services\GoogleDriveService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SyncChangesJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public GoogleAccount $googleAccount
    ) {}

    public function handle(): void
    {
        $syncRun = SyncRun::create([
            'google_account_id' => $this->googleAccount->id,
            'monitored_folder_id' => null,
            'run_type' => 'incremental_sync',
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $driveService = new GoogleDriveService($this->googleAccount);

            $pageToken = $this->googleAccount->drive_start_page_token;
            $itemsScanned = 0;

            if (!$pageToken) {
                $syncRun->update([
                    'status' => 'failed',
                    'finished_at' => now(),
                    'error_message' => 'No start page token found. Please run initial index first.',
                ]);

                return;
            }

            do {
                $result = $driveService->getChanges($pageToken);
                $changes = $result['changes'];
                $pageToken = $result['nextPageToken'];
                $newStartPageToken = $result['newStartPageToken'];

                foreach ($changes as $change) {
                    $this->processChange($change);
                    $itemsScanned++;
                }

                if ($newStartPageToken) {
                    $this->googleAccount->update([
                        'drive_start_page_token' => $newStartPageToken,
                        'last_synced_at' => now(),
                    ]);
                }
            } while ($pageToken);

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

            $this->googleAccount->update([
                'status' => 'error',
                'last_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function processChange($change): void
    {
        $fileId = $change->getFileId();
        $removed = $change->getRemoved();
        $file = $change->getFile();

        $existingItem = DriveItem::where('google_account_id', $this->googleAccount->id)
            ->where('drive_file_id', $fileId)
            ->first();

        $monitoredFolder = $this->findMonitoredFolder($existingItem, $file);

        if ($removed || ($file && $file->getTrashed())) {
            $this->handleDeletedOrTrashed($existingItem, $file, $monitoredFolder);
        } elseif ($file) {
            $this->handleFileChange($existingItem, $file, $monitoredFolder);
        }
    }

    private function findMonitoredFolder($existingItem, $file)
    {
        if ($existingItem && $existingItem->monitored_folder_id) {
            return $existingItem->monitoredFolder;
        }

        if (!$file) {
            return null;
        }

        foreach ($this->googleAccount->monitoredFolders as $folder) {
            if ($this->isFileInFolder($file, $folder)) {
                return $folder;
            }
        }

        return null;
    }

    private function isFileInFolder($file, $folder): bool
    {
        if ($file->getId() === $folder->root_drive_file_id) {
            return true;
        }

        $parents = $file->getParents() ?? [];

        return in_array($folder->root_drive_file_id, $parents);
    }

    private function handleDeletedOrTrashed($existingItem, $file, $monitoredFolder): void
    {
        if (!$existingItem) {
            return;
        }

        $beforeData = $existingItem->toArray();
        $isTrashed = $file && $file->getTrashed();

        if ($isTrashed) {
            $existingItem->update([
                'trashed' => true,
                'last_seen_at' => now(),
            ]);

            DriveEvent::create([
                'google_account_id' => $this->googleAccount->id,
                'monitored_folder_id' => $monitoredFolder?->id,
                'drive_file_id' => $existingItem->drive_file_id,
                'event_type' => 'trashed',
                'change_source' => 'api_sync',
                'occurred_at' => now(),
                'before_json' => $beforeData,
                'after_json' => $existingItem->toArray(),
                'summary' => "File '{$existingItem->name}' was moved to trash",
            ]);
        } else {
            $existingItem->delete();

            DriveEvent::create([
                'google_account_id' => $this->googleAccount->id,
                'monitored_folder_id' => $monitoredFolder?->id,
                'drive_file_id' => $existingItem->drive_file_id,
                'event_type' => 'deleted',
                'change_source' => 'api_sync',
                'occurred_at' => now(),
                'before_json' => $beforeData,
                'after_json' => null,
                'summary' => "File '{$existingItem->name}' was permanently deleted",
            ]);
        }
    }

    private function handleFileChange($existingItem, $file, $monitoredFolder): void
    {
        $driveService = new GoogleDriveService($this->googleAccount);

        $newData = [
            'monitored_folder_id' => $monitoredFolder?->id,
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
        ];

        if ($existingItem) {
            $beforeData = $existingItem->toArray();
            $eventType = $this->determineEventType($beforeData, $newData);

            $existingItem->update($newData);

            DriveEvent::create([
                'google_account_id' => $this->googleAccount->id,
                'monitored_folder_id' => $monitoredFolder?->id,
                'drive_file_id' => $file->getId(),
                'event_type' => $eventType,
                'change_source' => 'api_sync',
                'occurred_at' => now(),
                'before_json' => $beforeData,
                'after_json' => $existingItem->fresh()->toArray(),
                'summary' => $this->generateSummary($eventType, $beforeData, $newData),
            ]);
        } else {
            $newItem = DriveItem::create([
                'google_account_id' => $this->googleAccount->id,
                'drive_file_id' => $file->getId(),
                ...$newData,
            ]);

            DriveEvent::create([
                'google_account_id' => $this->googleAccount->id,
                'monitored_folder_id' => $monitoredFolder?->id,
                'drive_file_id' => $file->getId(),
                'event_type' => 'created',
                'change_source' => 'api_sync',
                'occurred_at' => now(),
                'before_json' => null,
                'after_json' => $newItem->toArray(),
                'summary' => "File '{$newData['name']}' was created",
            ]);
        }
    }

    private function determineEventType(array $before, array $new): string
    {
        if ($before['name'] !== $new['name']) {
            return 'renamed';
        }

        if ($before['parent_drive_file_id'] !== $new['parent_drive_file_id']) {
            return 'moved';
        }

        if ($before['trashed'] && !$new['trashed']) {
            return 'restored';
        }

        if ($before['md5_checksum'] !== $new['md5_checksum']) {
            return 'updated';
        }

        return 'metadata_changed';
    }

    private function generateSummary(string $eventType, array $before, array $new): string
    {
        return match ($eventType) {
            'renamed' => "File renamed from '{$before['name']}' to '{$new['name']}'",
            'moved' => "File '{$new['name']}' was moved to a different folder",
            'restored' => "File '{$new['name']}' was restored from trash",
            'updated' => "File '{$new['name']}' content was updated",
            'metadata_changed' => "File '{$new['name']}' metadata was changed",
            default => "File '{$new['name']}' was modified",
        };
    }
}
