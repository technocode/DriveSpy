<?php

use App\Jobs\SyncChangesJob;
use App\Models\GoogleAccount;
use App\Models\SyncRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('job has 60 second timeout configured', function () {
    $googleAccount = GoogleAccount::factory()->create([
        'user_id' => $this->user->id,
        'drive_start_page_token' => 'test-token',
    ]);

    $job = new SyncChangesJob($googleAccount);

    expect($job->timeout)->toBe(60);
});

test('failed method updates sync run status to failed', function () {
    $googleAccount = GoogleAccount::factory()->create([
        'user_id' => $this->user->id,
        'drive_start_page_token' => 'test-token',
        'status' => 'active',
    ]);

    $syncRun = SyncRun::create([
        'google_account_id' => $googleAccount->id,
        'monitored_folder_id' => null,
        'run_type' => 'incremental_sync',
        'status' => 'running',
        'started_at' => now(),
    ]);

    $job = new SyncChangesJob($googleAccount);

    $reflection = new ReflectionClass($job);
    $property = $reflection->getProperty('syncRun');
    $property->setAccessible(true);
    $property->setValue($job, $syncRun);

    $exception = new Exception('Test timeout error');
    $job->failed($exception);

    $syncRun->refresh();
    $googleAccount->refresh();

    expect($syncRun->status)->toBe('failed')
        ->and($syncRun->finished_at)->not->toBeNull()
        ->and($syncRun->error_message)->toBe('Test timeout error')
        ->and($googleAccount->status)->toBe('error')
        ->and($googleAccount->last_error)->toBe('Test timeout error');
});

test('failed method handles null exception', function () {
    $googleAccount = GoogleAccount::factory()->create([
        'user_id' => $this->user->id,
        'drive_start_page_token' => 'test-token',
        'status' => 'active',
    ]);

    $syncRun = SyncRun::create([
        'google_account_id' => $googleAccount->id,
        'monitored_folder_id' => null,
        'run_type' => 'incremental_sync',
        'status' => 'running',
        'started_at' => now(),
    ]);

    $job = new SyncChangesJob($googleAccount);

    $reflection = new ReflectionClass($job);
    $property = $reflection->getProperty('syncRun');
    $property->setAccessible(true);
    $property->setValue($job, $syncRun);

    $job->failed(null);

    $syncRun->refresh();
    $googleAccount->refresh();

    expect($syncRun->status)->toBe('failed')
        ->and($syncRun->finished_at)->not->toBeNull()
        ->and($syncRun->error_message)->toBe('Job failed or timed out')
        ->and($googleAccount->status)->toBe('error')
        ->and($googleAccount->last_error)->toBe('Sync job failed or timed out');
});
