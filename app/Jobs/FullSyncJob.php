<?php

namespace App\Jobs;

use App\Models\DriveEvent;
use App\Models\DriveItem;
use App\Models\MonitoredFolder;
use App\Models\SyncRun;
use App\Services\GoogleDriveService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class FullSyncJob implements ShouldQueue
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
            'run_type' => 'full_sync',
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $driveService = new GoogleDriveService($this->monitoredFolder->googleAccount);

            // First, sync the monitored folder root itself
            $rootFolder = $driveService->getFileMetadata($this->monitoredFolder->root_drive_file_id);

            $owners = $rootFolder->getOwners() ?? [];
            $owner = ! empty($owners) ? $owners[0] : null;
            $ownerEmail = $owner?->getEmailAddress();
            $ownerName = $owner?->getDisplayName();

            $lastModifier = $rootFolder->getLastModifyingUser();
            $modifierEmail = $lastModifier?->getEmailAddress();
            $modifierName = $lastModifier?->getDisplayName();

            // Check if root folder exists (including soft-deleted)
            $rootItem = DriveItem::withTrashed()
                ->where('google_account_id', $this->monitoredFolder->google_account_id)
                ->where('drive_file_id', $this->monitoredFolder->root_drive_file_id)
                ->first();

            $rootData = [
                'monitored_folder_id' => $this->monitoredFolder->id,
                'parent_drive_file_id' => null,
                'name' => $this->monitoredFolder->root_name,
                'mime_type' => $rootFolder->getMimeType(),
                'is_folder' => true,
                'path_cache' => '/'.$this->monitoredFolder->root_name,
                'size_bytes' => null,
                'md5_checksum' => null,
                'modified_time' => $rootFolder->getModifiedTime(),
                'created_time' => $rootFolder->getCreatedTime(),
                'trashed' => $rootFolder->getTrashed() ?? false,
                'starred' => $rootFolder->getStarred() ?? false,
                'owned_by_me' => $rootFolder->getOwnedByMe() ?? false,
                'owners_json' => $rootFolder->getOwners(),
                'owner_email' => $ownerEmail,
                'owner_name' => $ownerName,
                'last_modifier_email' => $modifierEmail,
                'last_modifier_name' => $modifierName,
                'last_seen_at' => now(),
            ];

            if ($rootItem) {
                // Restore if soft-deleted
                if ($rootItem->trashed()) {
                    $rootItem->restore();
                }
                $rootItem->update($rootData);
            } else {
                // Create new
                $rootItem = DriveItem::create([
                    'google_account_id' => $this->monitoredFolder->google_account_id,
                    'drive_file_id' => $this->monitoredFolder->root_drive_file_id,
                    ...$rootData,
                ]);
            }

            // Get all files recursively from the monitored folder
            $files = $driveService->listAllFilesRecursively($this->monitoredFolder->root_drive_file_id);

            $itemsScanned = 1; // Count the root folder
            $itemsCreated = 0;
            $itemsUpdated = 0;
            $eventsCreated = 0;
            $seenFileIds = [$this->monitoredFolder->root_drive_file_id]; // Always include the monitored folder root

            foreach ($files as $file) {
                $fileId = $file->getId();
                $seenFileIds[] = $fileId;

                $existingItem = DriveItem::withTrashed()
                    ->where('google_account_id', $this->monitoredFolder->google_account_id)
                    ->where('drive_file_id', $fileId)
                    ->first();

                // Get owner information
                $owners = $file->getOwners() ?? [];
                $owner = ! empty($owners) ? $owners[0] : null;
                $ownerEmail = $owner?->getEmailAddress();
                $ownerName = $owner?->getDisplayName();

                // Get last modifier information
                $lastModifier = $file->getLastModifyingUser();
                $modifierEmail = $lastModifier?->getEmailAddress();
                $modifierName = $lastModifier?->getDisplayName();

                $newData = [
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
                    'owner_email' => $ownerEmail,
                    'owner_name' => $ownerName,
                    'last_modifier_email' => $modifierEmail,
                    'last_modifier_name' => $modifierName,
                    'last_seen_at' => now(),
                ];

                if ($existingItem) {
                    // Restore if soft-deleted
                    if ($existingItem->trashed()) {
                        $existingItem->restore();
                    }

                    // Check if anything changed
                    $beforeData = $existingItem->toArray();
                    $hasChanges = $this->hasChanges($beforeData, $newData);

                    if ($hasChanges) {
                        $eventType = $this->determineEventType($beforeData, $newData);
                        $existingItem->update($newData);

                        // Update path cache if name or parent changed
                        if ($eventType === 'renamed' || $eventType === 'moved') {
                            $this->buildPathCache($existingItem->fresh());
                        }

                        DriveEvent::create([
                            'google_account_id' => $this->monitoredFolder->google_account_id,
                            'monitored_folder_id' => $this->monitoredFolder->id,
                            'drive_file_id' => $fileId,
                            'event_type' => $eventType,
                            'change_source' => 'full_sync',
                            'occurred_at' => $file->getModifiedTime() ?? now(),
                            'before_json' => $beforeData,
                            'after_json' => $existingItem->fresh()->toArray(),
                            'summary' => $this->generateSummary($eventType, $beforeData, $newData),
                        ]);

                        $eventsCreated++;
                        $itemsUpdated++;
                    } else {
                        // Just update last_seen_at
                        $existingItem->update(['last_seen_at' => now()]);
                    }
                } else {
                    // New file - create it using updateOrCreate to avoid duplicates
                    $newItem = DriveItem::updateOrCreate(
                        [
                            'google_account_id' => $this->monitoredFolder->google_account_id,
                            'drive_file_id' => $fileId,
                        ],
                        $newData
                    );

                    // Build path cache for the new item
                    $this->buildPathCache($newItem);

                    DriveEvent::create([
                        'google_account_id' => $this->monitoredFolder->google_account_id,
                        'monitored_folder_id' => $this->monitoredFolder->id,
                        'drive_file_id' => $fileId,
                        'event_type' => 'created',
                        'change_source' => 'full_sync',
                        'occurred_at' => $file->getCreatedTime() ?? now(),
                        'before_json' => null,
                        'after_json' => $newItem->fresh()->toArray(),
                        'summary' => "File '{$newData['name']}' was discovered",
                    ]);

                    $eventsCreated++;
                    $itemsCreated++;
                }

                $itemsScanned++;
            }

            // Mark files that are no longer in Drive as deleted
            // Exclude the monitored folder root itself from deletion checks
            $deletedItems = DriveItem::where('google_account_id', $this->monitoredFolder->google_account_id)
                ->where('monitored_folder_id', $this->monitoredFolder->id)
                ->where('drive_file_id', '!=', $this->monitoredFolder->root_drive_file_id) // Never mark the root folder as deleted
                ->whereNotIn('drive_file_id', $seenFileIds)
                ->whereNull('deleted_at')
                ->get();

            foreach ($deletedItems as $deletedItem) {
                $beforeData = $deletedItem->toArray();
                $deletedItem->delete();

                DriveEvent::create([
                    'google_account_id' => $this->monitoredFolder->google_account_id,
                    'monitored_folder_id' => $this->monitoredFolder->id,
                    'drive_file_id' => $deletedItem->drive_file_id,
                    'event_type' => 'deleted',
                    'change_source' => 'full_sync',
                    'occurred_at' => now(),
                    'before_json' => $beforeData,
                    'after_json' => null,
                    'summary' => "File '{$deletedItem->name}' was removed",
                ]);

                $eventsCreated++;
            }

            $this->monitoredFolder->update([
                'last_indexed_at' => now(),
                'status' => 'active',
            ]);

            $syncRun->update([
                'status' => 'success',
                'finished_at' => now(),
                'items_scanned' => $itemsScanned,
                'events_created' => $eventsCreated,
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

            logger()->error('Full sync job failed', [
                'monitored_folder_id' => $this->monitoredFolder->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    private function hasChanges(array $before, array $new): bool
    {
        $fieldsToCheck = ['name', 'parent_drive_file_id', 'md5_checksum', 'trashed', 'modified_time'];

        foreach ($fieldsToCheck as $field) {
            $beforeValue = $before[$field] ?? null;
            $newValue = $new[$field] ?? null;

            if ($beforeValue !== $newValue) {
                return true;
            }
        }

        return false;
    }

    private function determineEventType(array $before, array $new): string
    {
        if ($before['name'] !== $new['name']) {
            return 'renamed';
        }

        if ($before['parent_drive_file_id'] !== $new['parent_drive_file_id']) {
            return 'moved';
        }

        if ($before['trashed'] && ! $new['trashed']) {
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

    private function buildPathCache(DriveItem $item): void
    {
        $pathParts = [];
        $current = $item;
        $maxDepth = 20; // Prevent infinite loops
        $depth = 0;

        // Walk up the parent chain to build the full path
        while ($current && $depth < $maxDepth) {
            array_unshift($pathParts, $current->name);

            if (! $current->parent_drive_file_id) {
                break;
            }

            // Get parent from database
            $current = DriveItem::where('google_account_id', $this->monitoredFolder->google_account_id)
                ->where('drive_file_id', $current->parent_drive_file_id)
                ->first();

            $depth++;
        }

        $path = '/'.implode('/', $pathParts);
        $item->update(['path_cache' => $path]);
    }
}
