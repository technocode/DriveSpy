<?php

namespace App\Console\Commands;

use App\Models\DriveEvent;
use App\Models\DriveItem;
use App\Models\GoogleAccount;
use App\Models\MonitoredFolder;
use App\Models\SyncRun;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ImportData extends Command
{
    protected $signature = 'drivespy:import 
                            {--file=drivespy-export.json : Import file name}
                            {--reset-passwords : Reset all user passwords to "password"}
                            {--skip-existing : Skip users that already exist}';

    protected $description = 'Import DriveSpy data from export file to localhost';

    private array $userMap = [];

    private array $accountMap = [];

    private array $folderMap = [];

    public function handle(): int
    {
        $this->info('📥 Importing DriveSpy data...');
        $this->newLine();

        $filename = $this->option('file');
        $filePath = storage_path('app/'.$filename);

        if (! file_exists($filePath)) {
            $this->error("❌ Export file not found: {$filePath}");
            $this->info('💡 Make sure you have exported data first using: php artisan drivespy:export');

            return self::FAILURE;
        }

        $data = json_decode(file_get_contents($filePath), true);

        if (! $data) {
            $this->error('❌ Invalid JSON file!');

            return self::FAILURE;
        }

        $this->info("📁 Importing from: {$filename}");
        $this->info("📅 Exported at: ".($data['exported_at'] ?? 'Unknown'));
        $this->newLine();

        if (! $this->confirm('This will import data into your local database. Continue?', true)) {
            $this->info('Import cancelled.');

            return self::SUCCESS;
        }

        DB::beginTransaction();

        try {
            $this->importUsers($data);
            $this->importGoogleAccounts($data);
            $this->importMonitoredFolders($data);

            if (isset($data['drive_items'])) {
                $this->importDriveItems($data);
            }

            if (isset($data['drive_events'])) {
                $this->importDriveEvents($data);
            }

            if (isset($data['sync_runs'])) {
                $this->importSyncRuns($data);
            }

            DB::commit();

            $this->newLine();
            $this->info('✅ Import completed successfully!');
            $this->newLine();
            $this->table(
                ['Type', 'Imported'],
                [
                    ['Users', count($data['users'] ?? [])],
                    ['Google Accounts', count($data['google_accounts'] ?? [])],
                    ['Monitored Folders', count($data['monitored_folders'] ?? [])],
                    ['Drive Items', count($data['drive_items'] ?? [])],
                    ['Drive Events', count($data['drive_events'] ?? [])],
                    ['Sync Runs', count($data['sync_runs'] ?? [])],
                ]
            );

            if ($this->option('reset-passwords')) {
                $this->newLine();
                $this->warn('⚠️  All user passwords have been reset to: password');
                $this->info('💡 You can now login with the exported user emails using password: password');
            } else {
                $this->newLine();
                $this->warn('⚠️  Note: User passwords from production are hashed and may not work.');
                $this->info('💡 Use --reset-passwords flag to reset all passwords to "password"');
            }

            $this->newLine();
            $this->warn('⚠️  Important: Google OAuth tokens are encrypted and environment-specific.');
            $this->info('💡 You will need to reconnect Google accounts in localhost for tokens to work.');
            $this->info('💡 The accounts are imported but tokens need to be refreshed via OAuth flow.');

            return self::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Import failed: '.$e->getMessage());
            $this->error('Stack trace: '.$e->getTraceAsString());

            return self::FAILURE;
        }
    }

    private function importUsers(array $data): void
    {
        $this->info('👤 Importing users...');

        $users = $data['users'] ?? [];
        $skipExisting = $this->option('skip-existing');
        $resetPasswords = $this->option('reset-passwords');

        $progressBar = $this->output->createProgressBar(count($users));
        $progressBar->start();

        $userMap = [];

        foreach ($users as $userData) {
            $email = $userData['email'];

            if ($skipExisting && User::where('email', $email)->exists()) {
                $existingUser = User::where('email', $email)->first();
                $userMap[$userData['id']] = $existingUser->id;
                $progressBar->advance();
                continue;
            }

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $userData['name'],
                    'is_admin' => $userData['is_admin'] ?? false,
                    'email_verified_at' => $userData['email_verified_at'] ?? null,
                    'password' => $resetPasswords ? Hash::make('password') : ($userData['password_hash'] ?? Hash::make('password')),
                    'two_factor_secret' => $userData['two_factor_secret'] ?? null,
                    'two_factor_recovery_codes' => $userData['two_factor_recovery_codes'] ?? null,
                    'two_factor_confirmed_at' => $userData['two_factor_confirmed_at'] ?? null,
                    'created_at' => $userData['created_at'] ?? now(),
                    'updated_at' => $userData['updated_at'] ?? now(),
                ]
            );

            $userMap[$userData['id']] = $user->id;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $this->userMap = $userMap;
    }

    private function importGoogleAccounts(array $data): void
    {
        $this->info('🔐 Importing Google accounts...');

        $accounts = $data['google_accounts'] ?? [];
        $userMap = $this->userMap ?? [];

        $progressBar = $this->output->createProgressBar(count($accounts));
        $progressBar->start();

        $accountMap = [];

        foreach ($accounts as $accountData) {
            $oldUserId = $accountData['user_id'];
            $newUserId = $userMap[$oldUserId] ?? null;

            if (! $newUserId) {
                $this->warn("⚠️  Skipping account {$accountData['email']} - user not found");
                $progressBar->advance();
                continue;
            }

            $account = GoogleAccount::updateOrCreate(
                [
                    'user_id' => $newUserId,
                    'google_user_id' => $accountData['google_user_id'],
                ],
                [
                    'email' => $accountData['email'],
                    'display_name' => $accountData['display_name'] ?? null,
                    'avatar_url' => $accountData['avatar_url'] ?? null,
                    'access_token' => $accountData['access_token_encrypted'] ?? null,
                    'refresh_token' => $accountData['refresh_token_encrypted'] ?? null,
                    'token_expires_at' => $accountData['token_expires_at'] ?? null,
                    'scopes' => $accountData['scopes'] ?? null,
                    'drive_start_page_token' => $accountData['drive_start_page_token'] ?? null,
                    'last_synced_at' => $accountData['last_synced_at'] ?? null,
                    'status' => $accountData['status'] ?? 'active',
                    'last_error' => $accountData['last_error'] ?? null,
                    'created_at' => $accountData['created_at'] ?? now(),
                    'updated_at' => $accountData['updated_at'] ?? now(),
                ]
            );

            $accountMap[$accountData['id']] = $account->id;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $this->accountMap = $accountMap;
    }

    private function importMonitoredFolders(array $data): void
    {
        $this->info('📁 Importing monitored folders...');

        $folders = $data['monitored_folders'] ?? [];
        $accountMap = $this->accountMap ?? [];

        $progressBar = $this->output->createProgressBar(count($folders));
        $progressBar->start();

        $folderMap = [];

        foreach ($folders as $folderData) {
            $oldAccountId = $folderData['google_account_id'];
            $newAccountId = $accountMap[$oldAccountId] ?? null;

            if (! $newAccountId) {
                $this->warn("⚠️  Skipping folder {$folderData['root_name']} - account not found");
                $progressBar->advance();
                continue;
            }

            $folder = MonitoredFolder::updateOrCreate(
                [
                    'google_account_id' => $newAccountId,
                    'root_drive_file_id' => $folderData['root_drive_file_id'],
                ],
                [
                    'root_name' => $folderData['root_name'],
                    'include_subfolders' => $folderData['include_subfolders'] ?? true,
                    'status' => $folderData['status'] ?? 'active',
                    'last_indexed_at' => $folderData['last_indexed_at'] ?? null,
                    'last_changed_at' => $folderData['last_changed_at'] ?? null,
                    'last_error' => $folderData['last_error'] ?? null,
                    'created_at' => $folderData['created_at'] ?? now(),
                    'updated_at' => $folderData['updated_at'] ?? now(),
                ]
            );

            $folderMap[$folderData['id']] = $folder->id;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $this->folderMap = $folderMap;
    }

    private function importDriveItems(array $data): void
    {
        $this->info('📄 Importing drive items...');

        $items = $data['drive_items'] ?? [];
        $accountMap = $this->accountMap ?? [];
        $folderMap = $this->folderMap ?? [];

        $progressBar = $this->output->createProgressBar(count($items));
        $progressBar->start();

        foreach ($items as $itemData) {
            $oldAccountId = $itemData['google_account_id'];
            $oldFolderId = $itemData['monitored_folder_id'];
            $newAccountId = $accountMap[$oldAccountId] ?? null;
            $newFolderId = $folderMap[$oldFolderId] ?? null;

            if (! $newAccountId || ! $newFolderId) {
                $progressBar->advance();
                continue;
            }

            DriveItem::updateOrCreate(
                [
                    'google_account_id' => $newAccountId,
                    'drive_file_id' => $itemData['drive_file_id'],
                ],
                array_merge($itemData, [
                    'google_account_id' => $newAccountId,
                    'monitored_folder_id' => $newFolderId,
                ])
            );

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
    }

    private function importDriveEvents(array $data): void
    {
        $this->info('📊 Importing drive events...');

        $events = $data['drive_events'] ?? [];
        $accountMap = $this->accountMap ?? [];
        $folderMap = $this->folderMap ?? [];

        $progressBar = $this->output->createProgressBar(count($events));
        $progressBar->start();

        foreach ($events as $eventData) {
            $oldAccountId = $eventData['google_account_id'];
            $oldFolderId = $eventData['monitored_folder_id'];
            $newAccountId = $accountMap[$oldAccountId] ?? null;
            $newFolderId = $folderMap[$oldFolderId] ?? null;

            if (! $newAccountId || ! $newFolderId) {
                $progressBar->advance();
                continue;
            }

            DriveEvent::create(array_merge($eventData, [
                'google_account_id' => $newAccountId,
                'monitored_folder_id' => $newFolderId,
            ]));

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
    }

    private function importSyncRuns(array $data): void
    {
        $this->info('🔄 Importing sync runs...');

        $syncs = $data['sync_runs'] ?? [];
        $accountMap = $this->accountMap ?? [];
        $folderMap = $this->folderMap ?? [];

        $progressBar = $this->output->createProgressBar(count($syncs));
        $progressBar->start();

        foreach ($syncs as $syncData) {
            $oldAccountId = $syncData['google_account_id'];
            $oldFolderId = $syncData['monitored_folder_id'];
            $newAccountId = $accountMap[$oldAccountId] ?? null;
            $newFolderId = $folderMap[$oldFolderId] ?? null;

            if (! $newAccountId || ! $newFolderId) {
                $progressBar->advance();
                continue;
            }

            SyncRun::create(array_merge($syncData, [
                'google_account_id' => $newAccountId,
                'monitored_folder_id' => $newFolderId,
            ]));

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
    }
}

