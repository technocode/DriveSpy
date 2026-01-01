<?php

namespace App\Jobs;

use App\Models\DriveItem;
use App\Models\MonitoredFolder;
use App\Models\SyncRun;
use App\Services\GoogleDriveService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class InitialIndexJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;

    public function __construct(
        public MonitoredFolder $monitoredFolder
    ) {}

    public function failed(?Throwable $exception): void
    {
        $errorMessage = $exception?->getMessage() ?? 'Initial indexing job failed or timed out';

        $this->monitoredFolder->update([
            'status' => 'error',
            'last_error' => $errorMessage,
        ]);

        logger()->error('InitialIndexJob failed', [
            'monitored_folder_id' => $this->monitoredFolder->id,
            'folder_name' => $this->monitoredFolder->root_name,
            'error' => $errorMessage,
            'trace' => $exception?->getTraceAsString(),
        ]);
    }

    public function handle(): void
    {
        logger()->info('===== INITIAL INDEX JOB STARTED =====', [
            'monitored_folder_id' => $this->monitoredFolder->id,
            'folder_name' => $this->monitoredFolder->root_name,
            'root_drive_file_id' => $this->monitoredFolder->root_drive_file_id,
        ]);

        $syncRun = SyncRun::create([
            'google_account_id' => $this->monitoredFolder->google_account_id,
            'monitored_folder_id' => $this->monitoredFolder->id,
            'run_type' => 'initial_index',
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            logger()->info('Initializing Google Drive service');
            $driveService = new GoogleDriveService($this->monitoredFolder->googleAccount);

            logger()->info('Fetching root folder metadata', [
                'root_drive_file_id' => $this->monitoredFolder->root_drive_file_id,
            ]);

            // First, create the monitored folder root as a drive_item for complete hierarchy
            $rootFolder = $driveService->getFileMetadata($this->monitoredFolder->root_drive_file_id);

            logger()->info('Root folder metadata fetched successfully', [
                'folder_name' => $rootFolder->getName(),
                'mime_type' => $rootFolder->getMimeType(),
            ]);

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
                'parent_drive_file_id' => null, // Root has no parent in our system
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

            logger()->info('Starting incremental folder processing');

            // Process folders incrementally instead of loading everything into memory
            $itemsScanned = 1; // Count the root folder
            $foldersToProcess = [$this->monitoredFolder->root_drive_file_id];
            $foldersProcessed = 0;

            while (! empty($foldersToProcess)) {
                $currentFolderId = array_shift($foldersToProcess);
                $pageToken = null;
                $filesInFolder = 0;

                logger()->info('Processing folder', [
                    'folder_id' => $currentFolderId,
                    'folders_remaining' => count($foldersToProcess),
                    'items_scanned_so_far' => $itemsScanned,
                ]);

                do {
                    $result = $driveService->getFolderContents($currentFolderId, $pageToken);
                    $files = $result['files'];
                    $pageToken = $result['nextPageToken'];

                    foreach ($files as $file) {
                        // Get owner information
                        $owners = $file->getOwners() ?? [];
                        $owner = ! empty($owners) ? $owners[0] : null;
                        $ownerEmail = $owner?->getEmailAddress();
                        $ownerName = $owner?->getDisplayName();

                        // Get last modifier information
                        $lastModifier = $file->getLastModifyingUser();
                        $modifierEmail = $lastModifier?->getEmailAddress();
                        $modifierName = $lastModifier?->getDisplayName();

                        $isFolder = $driveService->isFolder($file);

                        $item = DriveItem::updateOrCreate(
                            [
                                'google_account_id' => $this->monitoredFolder->google_account_id,
                                'drive_file_id' => $file->getId(),
                            ],
                            [
                                'monitored_folder_id' => $this->monitoredFolder->id,
                                'parent_drive_file_id' => $file->getParents()[0] ?? null,
                                'name' => $file->getName(),
                                'mime_type' => $file->getMimeType(),
                                'is_folder' => $isFolder,
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
                            ]
                        );

                        // Build path cache for this item
                        $this->buildPathCache($item);

                        $itemsScanned++;
                        $filesInFolder++;

                        // Add subfolder to processing queue
                        if ($isFolder) {
                            $foldersToProcess[] = $file->getId();
                        }

                        // Log progress every 100 files
                        if ($itemsScanned % 100 === 0) {
                            logger()->info('Indexing progress', [
                                'items_scanned' => $itemsScanned,
                                'folders_remaining' => count($foldersToProcess),
                            ]);
                        }
                    }
                } while ($pageToken);

                $foldersProcessed++;

                logger()->info('Folder processed', [
                    'folder_id' => $currentFolderId,
                    'files_in_folder' => $filesInFolder,
                    'folders_processed' => $foldersProcessed,
                    'total_items_scanned' => $itemsScanned,
                ]);
            }

            logger()->info('Getting start page token for incremental sync');
            $startPageToken = $driveService->getStartPageToken();

            $this->monitoredFolder->googleAccount->update([
                'drive_start_page_token' => $startPageToken,
                'last_synced_at' => now(),
            ]);

            $this->monitoredFolder->update([
                'last_indexed_at' => now(),
                'status' => 'active',
            ]);

            $syncRun->update([
                'status' => 'success',
                'finished_at' => now(),
                'items_scanned' => $itemsScanned,
            ]);

            logger()->info('===== INITIAL INDEX JOB COMPLETED =====', [
                'monitored_folder_id' => $this->monitoredFolder->id,
                'folder_name' => $this->monitoredFolder->root_name,
                'items_scanned' => $itemsScanned,
                'folders_processed' => $foldersProcessed,
                'duration' => now()->diffInSeconds($syncRun->started_at).' seconds',
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
