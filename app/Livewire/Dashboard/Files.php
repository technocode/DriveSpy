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

    protected $paginationTheme = 'tailwind';

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'account')]
    public ?int $filterAccountId = null;

    #[Url(as: 'folder')]
    public ?int $filterFolderId = null;

    #[Url(as: 'owner')]
    public string $filterOwner = '';

    #[Url(as: 'modified')]
    public string $filterModifiedTime = 'all';

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

    public function updatingFilterOwner(): void
    {
        $this->resetPage();
    }

    public function updatingFilterModifiedTime(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'filterAccountId', 'filterFolderId', 'filterOwner', 'filterModifiedTime']);
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
            ->where('trashed', false)
            ->where('is_folder', false);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('path_cache', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterAccountId) {
            $query->where('google_account_id', $this->filterAccountId);
        }

        if ($this->filterFolderId) {
            $query->where('monitored_folder_id', $this->filterFolderId);
        }

        if ($this->filterOwner) {
            $query->where(function ($q) {
                $q->where('owner_email', 'like', '%'.$this->filterOwner.'%')
                    ->orWhere('owner_name', 'like', '%'.$this->filterOwner.'%');
            });
        }

        if ($this->filterModifiedTime !== 'all') {
            match ($this->filterModifiedTime) {
                'today' => $query->whereDate('modified_time', today()),
                'last_7_days' => $query->where('modified_time', '>=', now()->subDays(7)),
                'last_30_days' => $query->where('modified_time', '>=', now()->subDays(30)),
                'last_90_days' => $query->where('modified_time', '>=', now()->subDays(90)),
                'last_year' => $query->where('modified_time', '>=', now()->subYear()),
                default => null,
            };
        }

        $items = $query
            ->latest('modified_time')
            ->paginate(20);

        $googleAccounts = auth()->user()->googleAccounts()->get();

        $monitoredFolders = $this->filterAccountId
            ? auth()->user()->monitoredFolders()->where('google_account_id', $this->filterAccountId)->get()
            : auth()->user()->monitoredFolders()->get();

        $owners = auth()->user()
            ->driveItems()
            ->select('owner_email', 'owner_name')
            ->whereNotNull('owner_email')
            ->distinct()
            ->orderBy('owner_email')
            ->get();

        return view('livewire.dashboard.files', [
            'items' => $items,
            'googleAccounts' => $googleAccounts,
            'monitoredFolders' => $monitoredFolders,
            'owners' => $owners,
        ]);
    }
}
