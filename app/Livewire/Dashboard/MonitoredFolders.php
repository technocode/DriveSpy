<?php

namespace App\Livewire\Dashboard;

use App\Jobs\FullSyncJob;
use App\Jobs\InitialIndexJob;
use App\Jobs\SyncChangesJob;
use App\Models\GoogleAccount;
use App\Models\MonitoredFolder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app.sidebar')]

class MonitoredFolders extends Component
{
    use WithPagination;

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public ?int $selectedAccountId = null;

    public ?string $selectedFolderId = null;

    public ?string $selectedFolderName = null;

    public bool $includeSubfolders = true;

    public ?int $editingFolderId = null;

    public function openCreateModal(): void
    {
        $this->reset(['selectedAccountId', 'selectedFolderId', 'selectedFolderName', 'includeSubfolders']);
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->reset(['selectedAccountId', 'selectedFolderId', 'selectedFolderName', 'includeSubfolders']);
    }

    public function updatedSelectedAccountId(): void
    {
        $this->reset(['selectedFolderId', 'selectedFolderName']);

        if ($this->selectedAccountId) {
            $this->dispatch('mountPicker', accountId: $this->selectedAccountId);
        }
    }

    #[On('folder-selected')]
    public function handleFolderSelected(string $folderId, string $folderName): void
    {
        $this->selectedFolderId = $folderId;
        $this->selectedFolderName = $folderName;
    }

    public function create(): void
    {
        $this->validate([
            'selectedAccountId' => 'required|exists:google_accounts,id',
            'selectedFolderId' => 'required|string',
            'selectedFolderName' => 'required|string',
            'includeSubfolders' => 'boolean',
        ]);

        $account = GoogleAccount::findOrFail($this->selectedAccountId);
        $this->authorize('view', $account);

        $existingFolder = MonitoredFolder::where('google_account_id', $this->selectedAccountId)
            ->where('root_drive_file_id', $this->selectedFolderId)
            ->first();

        if ($existingFolder) {
            session()->flash('error', 'This folder is already being monitored.');

            return;
        }

        $folder = MonitoredFolder::create([
            'google_account_id' => $this->selectedAccountId,
            'root_drive_file_id' => $this->selectedFolderId,
            'root_name' => $this->selectedFolderName,
            'include_subfolders' => $this->includeSubfolders,
            'status' => 'indexing',
        ]);

        InitialIndexJob::dispatch($folder);

        session()->flash('success', 'Folder monitoring started! Initial indexing has been queued and will begin shortly.');
        $this->closeCreateModal();
        $this->resetPage();
    }

    public function openEditModal(MonitoredFolder $folder): void
    {
        $this->authorize('update', $folder);

        $this->editingFolderId = $folder->id;
        $this->includeSubfolders = $folder->include_subfolders;
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->reset(['editingFolderId', 'includeSubfolders']);
    }

    public function update(): void
    {
        $this->validate([
            'includeSubfolders' => 'boolean',
        ]);

        $folder = MonitoredFolder::findOrFail($this->editingFolderId);
        $this->authorize('update', $folder);

        $folder->update([
            'include_subfolders' => $this->includeSubfolders,
        ]);

        session()->flash('success', 'Folder settings updated successfully!');
        $this->closeEditModal();
    }

    public function startInitialIndex(MonitoredFolder $folder): void
    {
        $this->authorize('view', $folder->googleAccount);

        if ($folder->last_indexed_at !== null) {
            session()->flash('error', 'This folder has already been indexed. Use reindex to scan again.');

            return;
        }

        if ($folder->status === 'indexing') {
            session()->flash('error', 'Initial indexing is already in progress for this folder.');

            return;
        }

        $folder->update(['status' => 'indexing']);
        InitialIndexJob::dispatch($folder);

        session()->flash('success', 'Initial indexing has been queued! Files will appear once the indexing completes.');
    }

    public function syncFolder(MonitoredFolder $folder): void
    {
        $this->authorize('view', $folder->googleAccount);

        if (! $folder->last_indexed_at) {
            session()->flash('error', 'Please run initial indexing first before syncing changes.');

            return;
        }

        SyncChangesJob::dispatch($folder->googleAccount);

        session()->flash('success', 'Incremental sync has been queued! Changes will be detected and logged shortly.');
    }

    public function fullSyncFolder(MonitoredFolder $folder): void
    {
        $this->authorize('view', $folder->googleAccount);

        if (! $folder->last_indexed_at) {
            session()->flash('error', 'Please run initial indexing first before doing a full sync.');

            return;
        }

        FullSyncJob::dispatch($folder);

        $folder->update(['status' => 'syncing']);

        session()->flash('success', 'Full sync has been queued! This will scan all files recursively and may take a while for large folders.');
    }

    public function delete(MonitoredFolder $folder): void
    {
        $this->authorize('delete', $folder);

        $folder->delete();

        session()->flash('success', 'Folder monitoring stopped successfully!');
        $this->resetPage();
    }

    public function render()
    {
        $folders = auth()->user()
            ->monitoredFolders()
            ->with('googleAccount')
            ->withCount(['driveItems', 'syncRuns'])
            ->latest()
            ->paginate(10);

        $googleAccounts = auth()->user()
            ->googleAccounts()
            ->where('status', 'active')
            ->get();

        return view('livewire.dashboard.monitored-folders', [
            'folders' => $folders,
            'googleAccounts' => $googleAccounts,
        ]);
    }
}
