<?php

namespace App\Console\Commands;

use App\Models\DriveEvent;
use App\Models\DriveItem;
use App\Models\GoogleAccount;
use App\Models\MonitoredFolder;
use App\Models\SyncRun;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportData extends Command
{
    protected $signature = 'drivespy:export 
                            {--file=drivespy-export.json : Export file name}
                            {--include-items : Include drive items (can be large)}
                            {--include-events : Include drive events (can be large)}
                            {--include-syncs : Include sync runs (can be large)}
                            {--user= : Export specific user by email}';

    protected $description = 'Export DriveSpy data (users, accounts, folders) for importing to localhost';

    public function handle(): int
    {
        $this->info('📦 Exporting DriveSpy data...');
        $this->newLine();

        $filename = $this->option('file');
        $includeItems = $this->option('include-items');
        $includeEvents = $this->option('include-events');
        $includeSyncs = $this->option('include-syncs');
        $userEmail = $this->option('user');

        $data = [
            'exported_at' => now()->toIso8601String(),
            'version' => '1.0',
            'users' => [],
            'google_accounts' => [],
            'monitored_folders' => [],
        ];

        if ($includeItems) {
            $data['drive_items'] = [];
        }
        if ($includeEvents) {
            $data['drive_events'] = [];
        }
        if ($includeSyncs) {
            $data['sync_runs'] = [];
        }

        $query = User::query();
        if ($userEmail) {
            $query->where('email', $userEmail);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->error('❌ No users found to export!');

            return self::FAILURE;
        }

        $this->info("Found {$users->count()} user(s) to export");
        $this->newLine();

        $progressBar = $this->output->createProgressBar($users->count());
        $progressBar->start();

        foreach ($users as $user) {
            $this->exportUser($user, $data, $includeItems, $includeEvents, $includeSyncs);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $filePath = storage_path('app/'.$filename);
        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));

        $fileSize = filesize($filePath);
        $fileSizeFormatted = $this->formatBytes($fileSize);

        $this->info("✅ Export completed successfully!");
        $this->newLine();
        $this->table(
            ['Type', 'Count'],
            [
                ['Users', count($data['users'])],
                ['Google Accounts', count($data['google_accounts'])],
                ['Monitored Folders', count($data['monitored_folders'])],
                ['Drive Items', $includeItems ? count($data['drive_items'] ?? []) : 'Excluded'],
                ['Drive Events', $includeEvents ? count($data['drive_events'] ?? []) : 'Excluded'],
                ['Sync Runs', $includeSyncs ? count($data['sync_runs'] ?? []) : 'Excluded'],
            ]
        );
        $this->newLine();
        $this->info("📁 Export file: {$filePath}");
        $this->info("📊 File size: {$fileSizeFormatted}");
        $this->newLine();
        $this->comment('💡 To import this data, run: php artisan drivespy:import --file='.$filename);

        return self::SUCCESS;
    }

    private function exportUser(User $user, array &$data, bool $includeItems, bool $includeEvents, bool $includeSyncs): void
    {
        $userData = $user->toArray();
        unset($userData['password']);

        $userData['password_hash'] = $user->password;
        $data['users'][] = $userData;

        $googleAccounts = $user->googleAccounts()->get();

        foreach ($googleAccounts as $account) {
            $accountData = $account->toArray();

            $accountData['access_token_encrypted'] = $account->access_token;
            $accountData['refresh_token_encrypted'] = $account->refresh_token;

            unset($accountData['access_token'], $accountData['refresh_token']);

            $data['google_accounts'][] = $accountData;

            $monitoredFolders = $account->monitoredFolders()->get();

            foreach ($monitoredFolders as $folder) {
                $folderData = $folder->toArray();
                $data['monitored_folders'][] = $folderData;

                if ($includeItems) {
                    $items = $folder->driveItems()->get();
                    foreach ($items as $item) {
                        $data['drive_items'][] = $item->toArray();
                    }
                }

                if ($includeEvents) {
                    $events = $folder->driveEvents()->get();
                    foreach ($events as $event) {
                        $data['drive_events'][] = $event->toArray();
                    }
                }

                if ($includeSyncs) {
                    $syncs = $folder->syncRuns()->get();
                    foreach ($syncs as $sync) {
                        $data['sync_runs'][] = $sync->toArray();
                    }
                }
            }
        }
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }
}

