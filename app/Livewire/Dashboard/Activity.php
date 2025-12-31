<?php

namespace App\Livewire\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app.sidebar')]

class Activity extends Component
{
    use WithPagination;

    #[Url(as: 'account')]
    public ?int $filterAccountId = null;

    #[Url(as: 'folder')]
    public ?int $filterFolderId = null;

    #[Url(as: 'type')]
    public string $filterEventType = 'all';

    #[Url(as: 'q')]
    public string $search = '';

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

    public function updatingFilterEventType(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'filterAccountId', 'filterFolderId', 'filterEventType']);
        $this->resetPage();
    }

    public function render()
    {
        $query = auth()->user()
            ->driveEvents()
            ->with(['googleAccount', 'monitoredFolder']);

        if ($this->filterAccountId) {
            $query->where('google_account_id', $this->filterAccountId);
        }

        if ($this->filterFolderId) {
            $query->where('monitored_folder_id', $this->filterFolderId);
        }

        if ($this->filterEventType !== 'all') {
            $query->where('event_type', $this->filterEventType);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('summary', 'like', '%'.$this->search.'%')
                    ->orWhere('drive_file_id', 'like', '%'.$this->search.'%')
                    ->orWhere('actor_email', 'like', '%'.$this->search.'%');
            });
        }

        $events = $query
            ->latest('updated_at')
            ->paginate(20);

        $googleAccounts = auth()->user()->googleAccounts()->get();

        $monitoredFolders = $this->filterAccountId
            ? auth()->user()->monitoredFolders()->where('google_account_id', $this->filterAccountId)->get()
            : auth()->user()->monitoredFolders()->get();

        $eventTypes = [
            'all' => 'All Events',
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'trashed' => 'Trashed',
            'restored' => 'Restored',
            'renamed' => 'Renamed',
            'moved' => 'Moved',
            'metadata_changed' => 'Metadata Changed',
        ];

        return view('livewire.dashboard.activity', [
            'events' => $events,
            'googleAccounts' => $googleAccounts,
            'monitoredFolders' => $monitoredFolders,
            'eventTypes' => $eventTypes,
        ]);
    }
}
