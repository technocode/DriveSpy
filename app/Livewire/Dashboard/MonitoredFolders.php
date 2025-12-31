<?php

namespace App\Livewire\Dashboard;

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

        MonitoredFolder::create([
            'google_account_id' => $this->selectedAccountId,
            'root_drive_file_id' => $this->selectedFolderId,
            'root_name' => $this->selectedFolderName,
            'include_subfolders' => $this->includeSubfolders,
            'status' => 'active',
        ]);

        session()->flash('success', 'Folder monitoring started successfully!');
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
