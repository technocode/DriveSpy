<?php

use App\Livewire\GoogleDriveFolderPicker;
use App\Models\GoogleAccount;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('renders successfully with google account', function () {
    $account = GoogleAccount::factory()->create(['user_id' => $this->user->id]);

    Livewire::test(GoogleDriveFolderPicker::class, ['googleAccountId' => $account->id])
        ->assertStatus(200)
        ->assertSet('googleAccountId', $account->id)
        ->assertSet('currentFolderId', 'root')
        ->assertSee('My Drive');
});

it('shows warning when no google account is selected', function () {
    Livewire::test(GoogleDriveFolderPicker::class)
        ->assertStatus(200)
        ->assertSee('Please select a Google Account first');
});

it('initializes with correct default state', function () {
    Livewire::test(GoogleDriveFolderPicker::class)
        ->assertSet('googleAccountId', null)
        ->assertSet('currentFolderId', 'root')
        ->assertSet('breadcrumbs', [])
        ->assertSet('folders', [])
        ->assertSet('loading', false)
        ->assertSet('error', null);
});

it('sets up breadcrumbs when mounted with google account', function () {
    $account = GoogleAccount::factory()->create(['user_id' => $this->user->id]);

    Livewire::test(GoogleDriveFolderPicker::class, ['googleAccountId' => $account->id])
        ->assertCount('breadcrumbs', 1)
        ->assertSet('breadcrumbs.0.id', 'root')
        ->assertSet('breadcrumbs.0.name', 'My Drive');
});

it('navigates into a subfolder and updates breadcrumbs', function () {
    $account = GoogleAccount::factory()->create(['user_id' => $this->user->id]);

    Livewire::test(GoogleDriveFolderPicker::class, ['googleAccountId' => $account->id])
        ->call('navigateToFolder', 'folder-123', 'Documents')
        ->assertSet('currentFolderId', 'folder-123')
        ->assertCount('breadcrumbs', 2)
        ->assertSet('breadcrumbs.1.id', 'folder-123')
        ->assertSet('breadcrumbs.1.name', 'Documents');
});

it('navigates through multiple folders', function () {
    $account = GoogleAccount::factory()->create(['user_id' => $this->user->id]);

    Livewire::test(GoogleDriveFolderPicker::class, ['googleAccountId' => $account->id])
        ->call('navigateToFolder', 'folder-1', 'Folder 1')
        ->call('navigateToFolder', 'folder-2', 'Folder 2')
        ->call('navigateToFolder', 'folder-3', 'Folder 3')
        ->assertSet('currentFolderId', 'folder-3')
        ->assertCount('breadcrumbs', 4);
});

it('navigates back using breadcrumbs', function () {
    $account = GoogleAccount::factory()->create(['user_id' => $this->user->id]);

    Livewire::test(GoogleDriveFolderPicker::class, ['googleAccountId' => $account->id])
        ->call('navigateToFolder', 'folder-1', 'Folder 1')
        ->call('navigateToFolder', 'folder-2', 'Folder 2')
        ->assertCount('breadcrumbs', 3)
        ->call('navigateToBreadcrumb', 0)
        ->assertSet('currentFolderId', 'root')
        ->assertCount('breadcrumbs', 1);
});

it('dispatches folder-selected event when folder is selected', function () {
    $account = GoogleAccount::factory()->create(['user_id' => $this->user->id]);

    Livewire::test(GoogleDriveFolderPicker::class, ['googleAccountId' => $account->id])
        ->call('selectFolder', 'selected-folder-id', 'Selected Folder')
        ->assertDispatched('folder-selected', folderId: 'selected-folder-id', folderName: 'Selected Folder');
});

it('resets state when google account is changed', function () {
    $account1 = GoogleAccount::factory()->create(['user_id' => $this->user->id]);
    $account2 = GoogleAccount::factory()->create(['user_id' => $this->user->id]);

    Livewire::test(GoogleDriveFolderPicker::class, ['googleAccountId' => $account1->id])
        ->call('navigateToFolder', 'folder-1', 'Folder 1')
        ->assertCount('breadcrumbs', 2)
        ->set('googleAccountId', $account2->id)
        ->assertSet('currentFolderId', 'root')
        ->assertCount('breadcrumbs', 1);
});

it('maintains correct breadcrumb trail when navigating', function () {
    $account = GoogleAccount::factory()->create(['user_id' => $this->user->id]);

    Livewire::test(GoogleDriveFolderPicker::class, ['googleAccountId' => $account->id])
        ->call('navigateToFolder', 'folder-1', 'Photos')
        ->call('navigateToFolder', 'folder-2', '2024')
        ->call('navigateToFolder', 'folder-3', 'January')
        ->assertSet('breadcrumbs', [
            ['id' => 'root', 'name' => 'My Drive'],
            ['id' => 'folder-1', 'name' => 'Photos'],
            ['id' => 'folder-2', 'name' => '2024'],
            ['id' => 'folder-3', 'name' => 'January'],
        ]);
});
