<?php

namespace App\Livewire\Dashboard;

use App\Models\DriveEvent;
use App\Models\SyncRun;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app.sidebar')]

class SyncHistory extends Component
{
    use WithPagination;

    #[Url(as: 'account')]
    public ?int $filterAccountId = null;

    #[Url(as: 'folder')]
    public ?int $filterFolderId = null;

    #[Url(as: 'status')]
    public string $filterStatus = 'all';

    public bool $showEventsModal = false;

    public ?SyncRun $selectedSyncRun = null;

    public function updatingFilterAccountId(): void
    {
        $this->resetPage();
        $this->filterFolderId = null;
    }

    public function updatingFilterFolderId(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['filterAccountId', 'filterFolderId', 'filterStatus']);
        $this->resetPage();
    }

    public function viewEvents(SyncRun $syncRun): void
    {
        $this->authorize('view', $syncRun);

        $this->selectedSyncRun = $syncRun->load([
            'googleAccount',
            'monitoredFolder',
        ]);

        $this->showEventsModal = true;
    }

    public function closeEventsModal(): void
    {
        $this->showEventsModal = false;
        $this->selectedSyncRun = null;
    }

    public function render()
    {
        $query = auth()->user()
            ->syncRuns()
            ->with(['googleAccount', 'monitoredFolder']);

        if ($this->filterAccountId) {
            $query->where('google_account_id', $this->filterAccountId);
        }

        if ($this->filterFolderId) {
            $query->where('monitored_folder_id', $this->filterFolderId);
        }

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        $syncRuns = $query
            ->latest('started_at')
            ->paginate(20);

        $googleAccounts = auth()->user()->googleAccounts()->get();

        $monitoredFolders = $this->filterAccountId
            ? auth()->user()->monitoredFolders()->where('google_account_id', $this->filterAccountId)->get()
            : auth()->user()->monitoredFolders()->get();

        $events = $this->selectedSyncRun
            ? DriveEvent::where('monitored_folder_id', $this->selectedSyncRun->monitored_folder_id)
                ->whereBetween('occurred_at', [
                    $this->selectedSyncRun->started_at,
                    $this->selectedSyncRun->finished_at ?? now(),
                ])
                ->latest('occurred_at')
                ->limit(50)
                ->get()
            : collect();

        return view('livewire.dashboard.sync-history', [
            'syncRuns' => $syncRuns,
            'googleAccounts' => $googleAccounts,
            'monitoredFolders' => $monitoredFolders,
            'events' => $events,
        ]);
    }
}
