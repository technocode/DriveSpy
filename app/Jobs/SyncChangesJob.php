<?php

namespace App\Jobs;

use App\Models\GoogleAccount;
use App\Models\MonitoredFolder;
use App\Models\SyncRun;
use App\Services\GoogleDriveService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SyncChangesJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 60;

    private ?SyncRun $syncRun = null;

    public function __construct(
        public GoogleAccount $googleAccount,
        public ?MonitoredFolder $monitoredFolder = null
    ) {}

    public function failed(?Throwable $exception): void
    {
        if ($this->syncRun) {
            $this->syncRun->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error_message' => $exception?->getMessage() ?? 'Job failed or timed out',
            ]);
        }

        $this->googleAccount->update([
            'status' => 'error',
            'last_error' => $exception?->getMessage() ?? 'Sync job failed or timed out',
        ]);
    }

    public function handle(): void
    {
        $this->syncRun = SyncRun::create([
            'google_account_id' => $this->googleAccount->id,
            'monitored_folder_id' => $this->monitoredFolder?->id,
            'run_type' => 'incremental_sync',
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $driveService = new GoogleDriveService($this->googleAccount);

            $pageToken = $this->googleAccount->drive_start_page_token;
            $changesFetched = 0;
            $batchSize = 5;
            $allChanges = [];

            if (! $pageToken) {
                $this->syncRun->update([
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

                logger()->info('===== GOOGLE DRIVE API RESPONSE =====', [
                    'changes_count' => count($changes),
                    'next_page_token' => $pageToken,
                    'new_start_page_token' => $newStartPageToken,
                    'changes_fetched_so_far' => $changesFetched,
                ]);

                foreach ($changes as $change) {
                    $file = $change->getFile();
                    $fileId = $change->getFileId();
                    $removed = $change->getRemoved();

                    $changeData = [
                        'fileId' => $fileId,
                        'removed' => $removed,
                        'file' => $file ? json_decode(json_encode($file), true) : null,
                    ];

                    $allChanges[] = $changeData;
                    $changesFetched++;

                    logger()->info("Queuing Change #{$changesFetched}", [
                        'file_id' => $fileId,
                        'removed' => $removed,
                    ]);
                }

                if ($newStartPageToken) {
                    $this->googleAccount->update([
                        'drive_start_page_token' => $newStartPageToken,
                        'last_synced_at' => now(),
                    ]);

                    logger()->info('Updated page token', [
                        'new_token' => $newStartPageToken,
                    ]);
                }
            } while ($pageToken);

            // Dispatch changes in batches of 5
            $batches = array_chunk($allChanges, $batchSize);

            logger()->info('Dispatching metadata processing jobs', [
                'total_changes' => count($allChanges),
                'batch_size' => $batchSize,
                'total_batches' => count($batches),
            ]);

            foreach ($batches as $batchIndex => $batch) {
                ProcessChangeMetadataJob::dispatch(
                    $this->googleAccount,
                    $this->syncRun,
                    $batch
                );

                logger()->info("Dispatched batch #{$batchIndex}", [
                    'batch_number' => $batchIndex + 1,
                    'changes_in_batch' => count($batch),
                ]);
            }

            // Only save sync run if files were actually scanned
            if ($changesFetched > 0) {
                $this->syncRun->update([
                    'status' => 'success',
                    'finished_at' => now(),
                    'items_scanned' => $changesFetched,
                    'changes_fetched' => $changesFetched,
                ]);
            } else {
                // Delete sync run if no changes detected
                $this->syncRun->delete();
                $this->syncRun = null;
            }

        } catch (Throwable $e) {
            $this->syncRun->update([
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
}
