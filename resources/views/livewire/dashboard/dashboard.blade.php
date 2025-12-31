<x-dashboard.layout :heading="__('Dashboard')" :subheading="__('Overview of your Google Drive monitoring')">
    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <x-dashboard.stat-card
            :label="__('Connected Accounts')"
            :value="$googleAccountsCount"
            icon="user-group"
        />

        <x-dashboard.stat-card
            :label="__('Monitored Folders')"
            :value="$monitoredFoldersCount"
            icon="folder"
        />

        <x-dashboard.stat-card
            :label="__('Total Files')"
            :value="$driveItemsCount"
            icon="document"
        />

        <x-dashboard.stat-card
            :label="__('Recent Syncs (7 days)')"
            :value="$recentSyncsCount"
            icon="arrow-path"
        />
    </div>

    {{-- Recent Activity --}}
    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
        <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
            <flux:heading size="lg">{{ __('Recent Sync Activity') }}</flux:heading>
        </div>

        @if ($recentSyncs->count() > 0)
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($recentSyncs as $sync)
                    <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <flux:text class="font-medium">
                                    {{ $sync->googleAccount->email }}
                                </flux:text>
                                <flux:text class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $sync->monitoredFolder->root_name ?? 'Unknown Folder' }}
                                </flux:text>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <flux:text class="text-sm">
                                        {{ $sync->changes_count ?? 0 }} {{ __('changes') }}
                                    </flux:text>
                                    <flux:text class="text-xs text-gray-500 dark:text-gray-500">
                                        {{ $sync->created_at->diffForHumans() }}
                                    </flux:text>
                                </div>
                                @if ($sync->status === 'completed')
                                    <flux:badge color="green" size="sm">{{ __('Completed') }}</flux:badge>
                                @elseif ($sync->status === 'failed')
                                    <flux:badge color="red" size="sm">{{ __('Failed') }}</flux:badge>
                                @else
                                    <flux:badge color="yellow" size="sm">{{ __('In Progress') }}</flux:badge>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <flux:icon name="clock" variant="outline" class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-4" />
                <flux:text class="text-gray-600 dark:text-gray-400">
                    {{ __('No recent sync activity') }}
                </flux:text>
            </div>
        @endif
    </div>
</x-dashboard.layout>
