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

class ProcessChangeMetadataJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 60;

    public function __construct(
        public GoogleAccount $googleAccount,
        public SyncRun $syncRun,
        public array $changes
    ) {}

    public function handle(): void
    {
        $driveService = new GoogleDriveService($this->googleAccount);

        foreach ($this->changes as $changeData) {
            try {
                $this->processChange($changeData, $driveService);
            } catch (\Exception $e) {
                logger()->error('Failed to process change', [
                    'change_data' => $changeData,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                continue;
            }
        }
    }

    public function failed(?Throwable $exception): void
    {
        logger()->error('ProcessChangeMetadataJob failed', [
            'google_account_id' => $this->googleAccount->id,
            'sync_run_id' => $this->syncRun->id,
            'changes_count' => count($this->changes),
            'error' => $exception?->getMessage(),
        ]);
    }

    private function processChange(array $changeData, GoogleDriveService $driveService): void
    {
        $fileId = $changeData['fileId'];
        $removed = $changeData['removed'];
        $file = $changeData['file'] ?? null;

        logger()->info('Processing change for file', [
            'file_id' => $fileId,
            'removed' => $removed,
            'has_file_data' => $file !== null,
        ]);

        // Reconstruct the change object if we have file data
        if ($file && ! $removed) {
            logger()->info('Fetching full file metadata from API', [
                'file_id' => $fileId,
            ]);

            try {
                $file = $driveService->getFileMetadata($fileId);

                logger()->info('Full metadata fetched successfully', [
                    'file_id' => $fileId,
                    'file_name' => $file->getName(),
                    'parents' => $file->getParents() ?? [],
                    'mime_type' => $file->getMimeType(),
                    'trashed' => $file->getTrashed() ?? false,
                ]);
            } catch (\Exception $e) {
                logger()->warning('Failed to fetch file metadata', [
                    'file_id' => $fileId,
                    'error' => $e->getMessage(),
                ]);

                return;
            }
        }

        $existingItem = DriveItem::where('google_account_id', $this->googleAccount->id)
            ->where('drive_file_id', $fileId)
            ->first();

        $monitoredFolder = $this->findMonitoredFolder($existingItem, $file, $fileId);

        if (! $monitoredFolder && ! $existingItem) {
            logger()->debug('SKIPPING: No monitored folder found and no existing item', [
                'file_id' => $fileId,
            ]);

            return;
        }

        logger()->info('PROCESSING file change', [
            'file_id' => $fileId,
            'monitored_folder' => $monitoredFolder ? $monitoredFolder->root_name : 'none',
            'removed' => $removed,
            'has_file_data' => $file !== null,
            'existing_in_db' => $existingItem !== null,
        ]);

        if ($removed && ! $file) {
            $this->handlePermanentlyDeleted($existingItem, $fileId, $driveService);
        } elseif ($removed || ($file && $file->getTrashed())) {
            $this->handleDeletedOrTrashed($existingItem, $file, $monitoredFolder, $driveService);
        } elseif ($file) {
            $this->handleFileChange($existingItem, $file, $monitoredFolder, $driveService);
        }
    }

    private function findMonitoredFolder($existingItem, $file, string $fileId)
    {
        if ($existingItem && $existingItem->monitored_folder_id) {
            return $existingItem->monitoredFolder;
        }

        if (! $file) {
            return null;
        }

        $isRoot = $this->googleAccount->monitoredFolders()
            ->where('root_drive_file_id', $fileId)
            ->first();

        if ($isRoot) {
            return $isRoot;
        }

        $parents = is_array($file) ? ($file['parents'] ?? []) : ($file->getParents() ?? []);

        if (empty($parents)) {
            logger()->warning('SKIPPING: File in Drive root, not in monitored folder', [
                'file_id' => $fileId,
            ]);

            return null;
        }

        foreach ($this->googleAccount->monitoredFolders as $folder) {
            if (in_array($folder->root_drive_file_id, $parents)) {
                return $folder;
            }

            foreach ($parents as $parentId) {
                if ($this->isAncestorInMonitoredFolder($parentId, $folder)) {
                    return $folder;
                }
            }
        }

        return null;
    }

    private function isAncestorInMonitoredFolder(string $folderId, $monitoredFolder, int $depth = 0): bool
    {
        if ($depth > 10) {
            return false;
        }

        if ($folderId === $monitoredFolder->root_drive_file_id) {
            return true;
        }

        $driveItem = DriveItem::where('google_account_id', $this->googleAccount->id)
            ->where('drive_file_id', $folderId)
            ->where('monitored_folder_id', $monitoredFolder->id)
            ->where('is_folder', true)
            ->first();

        if ($driveItem) {
            if ($driveItem->parent_drive_file_id) {
                return $this->isAncestorInMonitoredFolder($driveItem->parent_drive_file_id, $monitoredFolder, $depth + 1);
            }

            return true;
        }

        try {
            $driveService = new GoogleDriveService($this->googleAccount);
            $parentFile = $driveService->getFileMetadata($folderId);
            $parentParents = $parentFile->getParents() ?? [];

            if (empty($parentParents)) {
                return false;
            }

            foreach ($parentParents as $parentParentId) {
                if ($this->isAncestorInMonitoredFolder($parentParentId, $monitoredFolder, $depth + 1)) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            logger()->debug('Failed to check parent in monitored folder', [
                'folder_id' => $folderId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        return false;
    }

    private function handlePermanentlyDeleted($existingItem, string $fileId, GoogleDriveService $driveService): void
    {
        if (! $existingItem) {
            return;
        }

        $beforeData = $existingItem->toArray();
        $originalMonitoredFolderId = $existingItem->monitored_folder_id;
        $fileName = $existingItem->name;

        $existingItem->delete();

        $actor = null;
        try {
            $actor = $driveService->getLastModifier($fileId);
        } catch (\Exception $e) {
            // Ignore if we can't get actor info
        }

        DriveEvent::create([
            'google_account_id' => $this->googleAccount->id,
            'monitored_folder_id' => $originalMonitoredFolderId,
            'drive_file_id' => $fileId,
            'event_type' => 'deleted',
            'change_source' => 'api_sync',
            'occurred_at' => now(),
            'actor_email' => $actor['email'] ?? null,
            'actor_name' => $actor['name'] ?? null,
            'before_json' => $beforeData,
            'after_json' => null,
            'summary' => "File '{$fileName}' was permanently deleted",
        ]);
    }

    private function handleDeletedOrTrashed($existingItem, $file, $monitoredFolder, GoogleDriveService $driveService): void
    {
        if (! $existingItem) {
            return;
        }

        $beforeData = $existingItem->toArray();
        $isTrashed = $file && $file->getTrashed();

        if ($isTrashed) {
            $actor = $driveService->getLastModifier($existingItem->drive_file_id);

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
                'occurred_at' => $file?->getTrashedTime() ?? $file?->getModifiedTime() ?? now(),
                'actor_email' => $actor['email'] ?? null,
                'actor_name' => $actor['name'] ?? null,
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

    private function handleFileChange($existingItem, $file, $monitoredFolder, GoogleDriveService $driveService): void
    {
        if (! $monitoredFolder) {
            if ($existingItem) {
                $beforeData = $existingItem->toArray();
                $originalMonitoredFolderId = $existingItem->monitored_folder_id;

                $existingItem->delete();

                $actor = null;
                try {
                    $actor = $driveService->getLastModifier($existingItem->drive_file_id);
                } catch (\Exception $e) {
                    // Ignore if we can't get actor info
                }

                DriveEvent::create([
                    'google_account_id' => $this->googleAccount->id,
                    'monitored_folder_id' => $originalMonitoredFolderId,
                    'drive_file_id' => $existingItem->drive_file_id,
                    'event_type' => 'deleted',
                    'change_source' => 'api_sync',
                    'occurred_at' => now(),
                    'actor_email' => $actor['email'] ?? null,
                    'actor_name' => $actor['name'] ?? null,
                    'before_json' => $beforeData,
                    'after_json' => null,
                    'summary' => "File '{$existingItem->name}' was removed from monitored folder",
                ]);
            }

            return;
        }

        $isTrashed = $file->getTrashed() ?? false;

        $owners = $file->getOwners() ?? [];
        $owner = ! empty($owners) ? $owners[0] : null;
        $ownerEmail = $owner?->getEmailAddress();
        $ownerName = $owner?->getDisplayName();

        $lastModifier = $file->getLastModifyingUser();
        $modifierEmail = $lastModifier?->getEmailAddress();
        $modifierName = $lastModifier?->getDisplayName();

        $newData = [
            'monitored_folder_id' => $monitoredFolder->id,
            'parent_drive_file_id' => $file->getParents()[0] ?? null,
            'name' => $file->getName(),
            'mime_type' => $file->getMimeType(),
            'is_folder' => $driveService->isFolder($file),
            'size_bytes' => $file->getSize(),
            'md5_checksum' => $file->getMd5Checksum(),
            'modified_time' => $file->getModifiedTime(),
            'created_time' => $file->getCreatedTime(),
            'trashed' => $isTrashed,
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
            $beforeData = $existingItem->toArray();

            if (! $beforeData['trashed'] && $isTrashed) {
                $actor = $driveService->getLastModifier($file->getId());

                $existingItem->update($newData);

                DriveEvent::create([
                    'google_account_id' => $this->googleAccount->id,
                    'monitored_folder_id' => $monitoredFolder->id,
                    'drive_file_id' => $file->getId(),
                    'event_type' => 'trashed',
                    'change_source' => 'api_sync',
                    'occurred_at' => $file->getTrashedTime() ?? $file->getModifiedTime() ?? now(),
                    'actor_email' => $actor['email'] ?? null,
                    'actor_name' => $actor['name'] ?? null,
                    'before_json' => $beforeData,
                    'after_json' => $existingItem->fresh()->toArray(),
                    'summary' => "File '{$file->getName()}' was moved to trash",
                ]);

                return;
            }

            $eventType = $this->determineEventType($beforeData, $newData);
            $actor = $driveService->getLastModifier($file->getId());

            $existingItem->update($newData);

            if ($eventType === 'renamed' || $eventType === 'moved') {
                $this->buildPathCache($existingItem->fresh());
            }

            DriveEvent::create([
                'google_account_id' => $this->googleAccount->id,
                'monitored_folder_id' => $monitoredFolder->id,
                'drive_file_id' => $file->getId(),
                'event_type' => $eventType,
                'change_source' => 'api_sync',
                'occurred_at' => $file->getModifiedTime() ?? now(),
                'actor_email' => $actor['email'] ?? null,
                'actor_name' => $actor['name'] ?? null,
                'before_json' => $beforeData,
                'after_json' => $existingItem->fresh()->toArray(),
                'summary' => $this->generateSummary($eventType, $beforeData, $newData),
            ]);

            if ($newData['is_folder'] && $eventType === 'metadata_changed') {
                $this->syncFolderContents($file->getId(), $monitoredFolder, $driveService);
            }
        } else {
            if (! $newData['last_modifier_email']) {
                $actor = $driveService->getLastModifier($file->getId());
                if ($actor) {
                    $newData['last_modifier_email'] = $actor['email'];
                    $newData['last_modifier_name'] = $actor['name'];
                }
            }

            $newItem = DriveItem::updateOrCreate(
                [
                    'google_account_id' => $this->googleAccount->id,
                    'drive_file_id' => $file->getId(),
                ],
                $newData
            );

            $this->buildPathCache($newItem);

            $actor = [
                'email' => $newData['last_modifier_email'],
                'name' => $newData['last_modifier_name'],
            ];

            DriveEvent::create([
                'google_account_id' => $this->googleAccount->id,
                'monitored_folder_id' => $monitoredFolder->id,
                'drive_file_id' => $file->getId(),
                'event_type' => 'created',
                'change_source' => 'api_sync',
                'occurred_at' => $file->getCreatedTime() ?? now(),
                'actor_email' => $actor['email'] ?? null,
                'actor_name' => $actor['name'] ?? null,
                'before_json' => null,
                'after_json' => $newItem->fresh()->toArray(),
                'summary' => "File '{$newData['name']}' was created",
            ]);
        }
    }

    private function determineEventType(array $before, array $new): string
    {
        if (! $before['trashed'] && $new['trashed']) {
            return 'trashed';
        }

        if ($before['name'] !== $new['name']) {
            return 'renamed';
        }

        if ($before['parent_drive_file_id'] !== $new['parent_drive_file_id'] && ! $new['trashed']) {
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
        $maxDepth = 20;
        $depth = 0;

        while ($current && $depth < $maxDepth) {
            array_unshift($pathParts, $current->name);

            if (! $current->parent_drive_file_id) {
                break;
            }

            $current = DriveItem::where('google_account_id', $this->googleAccount->id)
                ->where('drive_file_id', $current->parent_drive_file_id)
                ->first();

            $depth++;
        }

        $path = '/'.implode('/', $pathParts);
        $item->update(['path_cache' => $path]);
    }

    private function syncFolderContents(string $folderId, $monitoredFolder, GoogleDriveService $driveService): void
    {
        try {
            $pageToken = null;
            do {
                $result = $driveService->getFolderContents($folderId, $pageToken);
                $files = $result['files'];
                $pageToken = $result['nextPageToken'];

                foreach ($files as $file) {
                    $fileId = $file->getId();

                    $existingItem = DriveItem::where('google_account_id', $this->googleAccount->id)
                        ->where('drive_file_id', $fileId)
                        ->first();

                    if ($existingItem) {
                        continue;
                    }

                    $owners = $file->getOwners() ?? [];
                    $owner = ! empty($owners) ? $owners[0] : null;
                    $ownerEmail = $owner?->getEmailAddress();
                    $ownerName = $owner?->getDisplayName();

                    $lastModifier = $file->getLastModifyingUser();
                    $modifierEmail = $lastModifier?->getEmailAddress();
                    $modifierName = $lastModifier?->getDisplayName();

                    if (! $modifierEmail) {
                        $actor = $driveService->getLastModifier($fileId);
                        if ($actor) {
                            $modifierEmail = $actor['email'];
                            $modifierName = $actor['name'];
                        }
                    }

                    $newData = [
                        'monitored_folder_id' => $monitoredFolder->id,
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

                    $newItem = DriveItem::updateOrCreate(
                        [
                            'google_account_id' => $this->googleAccount->id,
                            'drive_file_id' => $fileId,
                        ],
                        $newData
                    );

                    $this->buildPathCache($newItem);

                    DriveEvent::create([
                        'google_account_id' => $this->googleAccount->id,
                        'monitored_folder_id' => $monitoredFolder->id,
                        'drive_file_id' => $fileId,
                        'event_type' => 'created',
                        'change_source' => 'api_sync',
                        'occurred_at' => $file->getCreatedTime() ?? now(),
                        'actor_email' => $modifierEmail,
                        'actor_name' => $modifierName,
                        'before_json' => null,
                        'after_json' => $newItem->fresh()->toArray(),
                        'summary' => "File '{$newData['name']}' was created",
                    ]);
                }
            } while ($pageToken);
        } catch (\Exception $e) {
            logger()->warning('Failed to sync folder contents', [
                'folder_id' => $folderId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
