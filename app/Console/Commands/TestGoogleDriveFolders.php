<?php

namespace App\Console\Commands;

use App\Models\GoogleAccount;
use App\Services\GoogleDriveService;
use Illuminate\Console\Command;

class TestGoogleDriveFolders extends Command
{
    protected $signature = 'test:google-drive-folders {google_account_id?}';

    protected $description = 'Test Google Drive folder fetching to diagnose issues';

    public function handle(): int
    {
        $this->info('🧪 Testing Google Drive Folder Fetching...');
        $this->newLine();

        // Get Google Account
        $accountId = $this->argument('google_account_id');

        if (! $accountId) {
            $accounts = GoogleAccount::where('status', 'active')->get();

            if ($accounts->isEmpty()) {
                $this->error('❌ No active Google accounts found!');
                $this->info('💡 Please connect a Google account first.');

                return self::FAILURE;
            }

            $this->info('Available Google Accounts:');
            foreach ($accounts as $account) {
                $this->line("  [{$account->id}] {$account->email}");
            }

            $accountId = $this->ask('Enter Google Account ID to test');
        }

        $account = GoogleAccount::find($accountId);

        if (! $account) {
            $this->error("❌ Google Account with ID {$accountId} not found!");

            return self::FAILURE;
        }

        $this->info("✅ Found Google Account: {$account->email}");
        $this->newLine();

        // Test 1: Check Token
        $this->info('📋 Test 1: Checking Access Token...');
        if (! $account->access_token) {
            $this->error('❌ No access token found!');

            return self::FAILURE;
        }
        $this->info('✅ Access token exists');

        if ($account->token_expires_at && $account->token_expires_at->isPast()) {
            $this->warn('⚠️  Access token is expired');
            if (! $account->refresh_token) {
                $this->error('❌ No refresh token available to renew access');

                return self::FAILURE;
            }
            $this->info('✅ Refresh token available');
        } else {
            $this->info('✅ Access token is valid');
        }
        $this->newLine();

        // Test 2: Initialize Google Drive Service
        $this->info('📋 Test 2: Initializing Google Drive Service...');
        try {
            $service = new GoogleDriveService($account);
            $this->info('✅ Service initialized successfully');
        } catch (\Exception $e) {
            $this->error("❌ Failed to initialize service: {$e->getMessage()}");

            return self::FAILURE;
        }
        $this->newLine();

        // Test 3: Fetch Root Folders
        $this->info('📋 Test 3: Fetching folders from My Drive (root)...');
        try {
            $result = $service->getFolders('root');

            $folderCount = count($result['folders']);
            $this->info("✅ Successfully fetched {$folderCount} folder(s)");

            if ($folderCount > 0) {
                $this->newLine();
                $this->info('📁 Folders found:');
                foreach ($result['folders'] as $index => $folder) {
                    $name = $folder->getName();
                    $id = $folder->getId();
                    $this->line('  '.($index + 1).". {$name} (ID: {$id})");

                    if ($index >= 9) {
                        $remaining = $folderCount - 10;
                        if ($remaining > 0) {
                            $this->line("  ... and {$remaining} more");
                        }
                        break;
                    }
                }

                if ($result['nextPageToken']) {
                    $this->info('📄 More folders available (pagination active)');
                }
            } else {
                $this->warn('⚠️  No folders found in My Drive');
                $this->info('💡 This could mean:');
                $this->line('   - Your Google Drive is empty (no folders, only files)');
                $this->line('   - The account has permission issues');
                $this->line('   - All items are in "Shared with me" (not in My Drive)');
            }
        } catch (\Exception $e) {
            $this->error("❌ Failed to fetch folders: {$e->getMessage()}");
            $this->newLine();
            $this->info('📋 Error Details:');
            $this->line('  Type: '.get_class($e));
            $this->line("  Message: {$e->getMessage()}");
            $this->line("  File: {$e->getFile()}:{$e->getLine()}");

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('🎉 All tests passed successfully!');
        $this->info('✅ Google Drive folder fetching is working correctly.');

        return self::SUCCESS;
    }
}
