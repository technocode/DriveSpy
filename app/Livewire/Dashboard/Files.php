<?php

namespace App\Livewire\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app.sidebar')]

class Files extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'account')]
    public ?int $filterAccountId = null;

    #[Url(as: 'folder')]
    public ?int $filterFolderId = null;

    #[Url(as: 'type')]
    public string $filterType = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterAccountId(): void
    {
        $this->resetPage();
        $this->filterFolderId = null;
    }

    public function updatingFilterFolderId(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'filterAccountId', 'filterFolderId', 'filterType']);
        $this->resetPage();
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
        $query = auth()->user()
            ->driveItems()
            ->with(['googleAccount', 'monitoredFolder'])
            ->where('trashed', false);

        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        if ($this->filterAccountId) {
            $query->where('google_account_id', $this->filterAccountId);
        }

        if ($this->filterFolderId) {
            $query->where('monitored_folder_id', $this->filterFolderId);
        }

        if ($this->filterType === 'folders') {
            $query->where('is_folder', true);
        } elseif ($this->filterType === 'files') {
            $query->where('is_folder', false);
        }

        $items = $query
            ->latest('modified_time')
            ->paginate(20);

        $googleAccounts = auth()->user()->googleAccounts()->get();

        $monitoredFolders = $this->filterAccountId
            ? auth()->user()->monitoredFolders()->where('google_account_id', $this->filterAccountId)->get()
            : auth()->user()->monitoredFolders()->get();

        return view('livewire.dashboard.files', [
            'items' => $items,
            'googleAccounts' => $googleAccounts,
            'monitoredFolders' => $monitoredFolders,
        ]);
    }
}
