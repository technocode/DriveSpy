<?php

namespace App\Livewire;

use App\Models\GoogleAccount;
use App\Services\GoogleDriveService;
use Livewire\Component;

class GoogleDriveFolderPicker extends Component
{
    public ?int $googleAccountId = null;

    public string $currentFolderId = 'root';

    public string $currentFolderName = 'My Drive';

    public array $breadcrumbs = [];

    public array $folders = [];

    public array $files = [];

    public bool $loading = false;

    public ?string $error = null;

    protected $listeners = ['mountPicker' => 'handleMountPicker'];

    public function mount(?int $googleAccountId = null): void
    {
        if ($googleAccountId) {
            $this->googleAccountId = $googleAccountId;
            $this->initializeBreadcrumbs();
            $this->loadContents();
        }
    }

    public function handleMountPicker($data = null): void
    {
        if (is_array($data) && isset($data['accountId'])) {
            $this->googleAccountId = $data['accountId'];
            $this->reset(['currentFolderId', 'breadcrumbs', 'folders', 'files', 'error']);
            $this->currentFolderId = 'root';
            $this->currentFolderName = 'My Drive';
            $this->initializeBreadcrumbs();
            $this->loadContents();
        } elseif (is_int($data)) {
            $this->googleAccountId = $data;
            $this->reset(['currentFolderId', 'breadcrumbs', 'folders', 'files', 'error']);
            $this->currentFolderId = 'root';
            $this->currentFolderName = 'My Drive';
            $this->initializeBreadcrumbs();
            $this->loadContents();
        }
    }

    public function updatedGoogleAccountId(): void
    {
        $this->reset(['currentFolderId', 'breadcrumbs', 'folders', 'files', 'error']);

        if ($this->googleAccountId) {
            $this->currentFolderId = 'root';
            $this->currentFolderName = 'My Drive';
            $this->initializeBreadcrumbs();
            $this->loadContents();
        }
    }

    protected function initializeBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            ['id' => 'root', 'name' => 'My Drive'],
        ];
    }

    public function loadContents(): void
    {
        if (! $this->googleAccountId) {
            return;
        }

        $this->loading = true;
        $this->error = null;

        try {
            $account = GoogleAccount::findOrFail($this->googleAccountId);
            $service = new GoogleDriveService($account);

            $result = $service->getFolderContentsWithFiles($this->currentFolderId);

            $this->folders = collect($result['folders'])->map(fn ($folder) => [
                'id' => $folder->getId(),
                'name' => $folder->getName(),
                'mimeType' => $folder->getMimeType(),
            ])->toArray();

            $this->files = collect($result['files'])->map(fn ($file) => [
                'id' => $file->getId(),
                'name' => $file->getName(),
                'mimeType' => $file->getMimeType(),
                'size' => $file->getSize(),
            ])->toArray();
        } catch (\Exception $e) {
            $this->error = 'Failed to load contents: '.$e->getMessage();
            $this->folders = [];
            $this->files = [];
            logger()->error('Google Drive folder picker error', [
                'account_id' => $this->googleAccountId,
                'folder_id' => $this->currentFolderId,
                'error' => $e->getMessage(),
            ]);
        } finally {
            $this->loading = false;
        }
    }

    public function openFolder(string $folderId, string $folderName): void
    {
        $this->currentFolderId = $folderId;
        $this->currentFolderName = $folderName;
        $this->breadcrumbs[] = ['id' => $folderId, 'name' => $folderName];
        $this->loadContents();
    }

    public function navigateToBreadcrumb(int $index): void
    {
        $this->breadcrumbs = array_slice($this->breadcrumbs, 0, $index + 1);
        $breadcrumb = $this->breadcrumbs[$index];
        $this->currentFolderId = $breadcrumb['id'];
        $this->currentFolderName = $breadcrumb['name'];
        $this->loadContents();
    }

    public function selectCurrentFolder(): void
    {
        $this->dispatch('folder-selected', folderId: $this->currentFolderId, folderName: $this->currentFolderName);
    }

    public function selectFolder(string $folderId, string $folderName): void
    {
        $this->dispatch('folder-selected', folderId: $folderId, folderName: $folderName);
    }

    public function formatFileSize($bytes): string
    {
        if (! $bytes) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }

    public function render()
    {
        return view('livewire.google-drive-folder-picker');
    }
}
