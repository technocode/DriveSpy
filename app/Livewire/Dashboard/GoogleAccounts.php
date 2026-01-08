<?php

namespace App\Livewire\Dashboard;

use App\Models\GoogleAccount;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app.sidebar')]

class GoogleAccounts extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public function reconnect(GoogleAccount $googleAccount): void
    {
        $this->authorize('update', $googleAccount);

        session(['reconnecting_google_account_id' => $googleAccount->id]);

        $this->redirect(route('google.oauth.redirect'), navigate: false);
    }

    public function disconnect(GoogleAccount $googleAccount): void
    {
        $this->authorize('delete', $googleAccount);

        $googleAccount->delete();

        session()->flash('success', 'Google account disconnected successfully!');

        $this->resetPage();
    }

    public function render()
    {
        $accounts = auth()->user()
            ->googleAccounts()
            ->withCount(['monitoredFolders', 'syncRuns'])
            ->latest()
            ->paginate(10);

        return view('livewire.dashboard.google-accounts', [
            'accounts' => $accounts,
        ]);
    }
}
