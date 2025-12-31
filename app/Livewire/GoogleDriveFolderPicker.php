<?php

namespace App\Livewire;

use App\Models\GoogleAccount;
use App\Services\GoogleDriveService;
use Livewire\Component;

class GoogleDriveFolderPicker extends Component
{
    public ?int $googleAccountId = null;

    public string $currentFolderId = 'root';

    public array $breadcrumbs = [];

    public array $folders = [];

    public bool $loading = false;

    public ?string $error = null;

    public function mount(?int $googleAccountId = null): void
    {
        $this->googleAccountId = $googleAccountId;

        if ($this->googleAccountId) {
            $this->breadcrumbs = [
                ['id' => 'root', 'name' => 'My Drive'],
            ];
            $this->loadFolders();
        }
    }

    public function updatedGoogleAccountId(): void
    {
        $this->currentFolderId = 'root';
        $this->breadcrumbs = [
            ['id' => 'root', 'name' => 'My Drive'],
        ];
        $this->folders = [];
        $this->error = null;

        if ($this->googleAccountId) {
            $this->loadFolders();
        }
    }

    public function loadFolders(): void
    {
        if (! $this->googleAccountId) {
            return;
        }

        $this->loading = true;
        $this->error = null;

        try {
            $account = GoogleAccount::findOrFail($this->googleAccountId);
            $service = new GoogleDriveService($account);

            $result = $service->getFolders($this->currentFolderId);
            $this->folders = collect($result['folders'])->map(fn ($folder) => [
                'id' => $folder->getId(),
                'name' => $folder->getName(),
            ])->toArray();
        } catch (\Exception $e) {
            $this->error = 'Failed to load folders: '.$e->getMessage();
            $this->folders = [];
        } finally {
            $this->loading = false;
        }
    }

    public function navigateToFolder(string $folderId, string $folderName): void
    {
        $this->currentFolderId = $folderId;
        $this->breadcrumbs[] = ['id' => $folderId, 'name' => $folderName];
        $this->loadFolders();
    }

    public function navigateToBreadcrumb(int $index): void
    {
        $this->breadcrumbs = array_slice($this->breadcrumbs, 0, $index + 1);
        $this->currentFolderId = $this->breadcrumbs[$index]['id'];
        $this->loadFolders();
    }

    public function selectFolder(string $folderId, string $folderName): void
    {
        $this->dispatch('folder-selected', folderId: $folderId, folderName: $folderName);
    }

    public function render()
    {
        return view('livewire.google-drive-folder-picker');
    }
}
