<?php

namespace App\Livewire\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app.sidebar')]

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();

        $googleAccountsCount = $user->googleAccounts()->count();
        $monitoredFoldersCount = $user->monitoredFolders()->count();
        $driveItemsCount = $user->driveItems()->count();
        $recentSyncsCount = $user->syncRuns()
            ->where('sync_runs.created_at', '>=', now()->subDays(7))
            ->count();

        $recentSyncs = $user->syncRuns()
            ->with(['googleAccount', 'monitoredFolder'])
            ->latest()
            ->limit(10)
            ->get();

        $recentEvents = $user->driveEvents()
            ->with(['googleAccount', 'monitoredFolder'])
            ->latest('occurred_at')
            ->limit(10)
            ->get();

        return view('livewire.dashboard.dashboard', [
            'googleAccountsCount' => $googleAccountsCount,
            'monitoredFoldersCount' => $monitoredFoldersCount,
            'driveItemsCount' => $driveItemsCount,
            'recentSyncsCount' => $recentSyncsCount,
            'recentSyncs' => $recentSyncs,
            'recentEvents' => $recentEvents,
        ]);
    }
}
