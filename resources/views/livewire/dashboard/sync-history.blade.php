<x-dashboard.layout :heading="__('Sync History')" :subheading="__('View the history of synchronization runs and events')">
    {{-- Filters --}}
    <div class="mb-6 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            {{-- Account Filter --}}
            <div>
                <flux:select wire:model.live="filterAccountId" :placeholder="__('All accounts')">
                    <option value="">{{ __('All accounts') }}</option>
                    @foreach ($googleAccounts as $account)
                        <option value="{{ $account->id }}">{{ $account->email }}</option>
                    @endforeach
                </flux:select>
            </div>

            {{-- Folder Filter --}}
            <div>
                <flux:select wire:model.live="filterFolderId" :placeholder="__('All folders')">
                    <option value="">{{ __('All folders') }}</option>
                    @foreach ($monitoredFolders as $folder)
                        <option value="{{ $folder->id }}">{{ $folder->root_name }}</option>
                    @endforeach
                </flux:select>
            </div>

            {{-- Status Filter --}}
            <div>
                <flux:select wire:model.live="filterStatus">
                    <option value="all">{{ __('All statuses') }}</option>
                    <option value="running">{{ __('Running') }}</option>
                    <option value="completed">{{ __('Completed') }}</option>
                    <option value="failed">{{ __('Failed') }}</option>
                </flux:select>
            </div>
        </div>

        {{-- Clear Filters --}}
        @if ($filterAccountId || $filterFolderId || $filterStatus !== 'all')
            <div class="mt-3">
                <flux:button size="sm" variant="ghost" wire:click="clearFilters">
                    {{ __('Clear all filters') }}
                </flux:button>
            </div>
        @endif
    </div>

    {{-- Sync Runs Table --}}
    @if ($syncRuns->isEmpty())
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-12 text-center">
            <flux:icon name="clock" variant="outline" class="w-16 h-16 mx-auto mb-4 text-gray-400 dark:text-gray-600" />
            <flux:heading class="mb-2">{{ __('No Sync History') }}</flux:heading>
            <flux:text class="text-gray-600 dark:text-gray-400">
                @if ($filterAccountId || $filterFolderId || $filterStatus !== 'all')
                    {{ __('Try adjusting your filters.') }}
                @else
                    {{ __('Sync runs will appear here once monitoring starts.') }}
                @endif
            </flux:text>
        </div>
    @else
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <flux:text class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                                    {{ __('Account / Folder') }}
                                </flux:text>
                            </th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">
                                <flux:text class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                                    {{ __('Type') }}
                                </flux:text>
                            </th>
                            <th class="px-4 py-3 text-left hidden lg:table-cell">
                                <flux:text class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                                    {{ __('Stats') }}
                                </flux:text>
                            </th>
                            <th class="px-4 py-3 text-left">
                                <flux:text class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                                    {{ __('Status') }}
                                </flux:text>
                            </th>
                            <th class="px-4 py-3 text-left hidden lg:table-cell">
                                <flux:text class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                                    {{ __('Started') }}
                                </flux:text>
                            </th>
                            <th class="px-4 py-3 text-left">
                                <flux:text class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                                    {{ __('Actions') }}
                                </flux:text>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($syncRuns as $syncRun)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3">
                                    <div>
                                        <flux:text class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                            {{ $syncRun->googleAccount->email }}
                                        </flux:text>
                                        <flux:text class="text-xs text-gray-600 dark:text-gray-400 truncate">
                                            {{ $syncRun->monitoredFolder?->root_name ?? __('All Folders') }}
                                        </flux:text>
                                    </div>
                                </td>
                                <td class="px-4 py-3 hidden md:table-cell">
                                    <flux:text class="text-sm text-gray-600 dark:text-gray-400 capitalize">
                                        {{ $syncRun->run_type }}
                                    </flux:text>
                                </td>
                                <td class="px-4 py-3 hidden lg:table-cell">
                                    <div class="text-xs text-gray-600 dark:text-gray-400">
                                        <div>{{ __('Scanned:') }} {{ $syncRun->items_scanned ?? 0 }}</div>
                                        <div>{{ __('Events:') }} {{ $syncRun->events_created ?? 0 }}</div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($syncRun->status === 'completed')
                                        <flux:badge variant="success">{{ __('Completed') }}</flux:badge>
                                    @elseif ($syncRun->status === 'running')
                                        <flux:badge variant="info">{{ __('Running') }}</flux:badge>
                                    @elseif ($syncRun->status === 'failed')
                                        <flux:badge variant="danger">{{ __('Failed') }}</flux:badge>
                                    @else
                                        <flux:badge>{{ $syncRun->status }}</flux:badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3 hidden lg:table-cell">
                                    <flux:text class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $syncRun->started_at?->diffForHumans() ?? '—' }}
                                    </flux:text>
                                </td>
                                <td class="px-4 py-3">
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        wire:click="viewEvents({{ $syncRun->id }})"
                                    >
                                        {{ __('View Events') }}
                                    </flux:button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $syncRuns->links() }}
            </div>
        </div>

        {{-- Results count --}}
        <div class="mt-4 text-center">
            <flux:text class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('Showing :from to :to of :total results', [
                    'from' => $syncRuns->firstItem() ?? 0,
                    'to' => $syncRuns->lastItem() ?? 0,
                    'total' => $syncRuns->total(),
                ]) }}
            </flux:text>
        </div>
    @endif

    {{-- Events Modal --}}
    <flux:modal :open="$showEventsModal" wire:model="showEventsModal" class="max-w-4xl">
        @if ($selectedSyncRun)
            <div class="mb-4">
                <flux:heading>{{ __('Sync Events') }}</flux:heading>
                <flux:text class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $selectedSyncRun->googleAccount->email }} - {{ $selectedSyncRun->monitoredFolder?->root_name ?? __('All Folders') }}
                </flux:text>
            </div>

            {{-- Sync Run Details --}}
            <div class="mb-4 p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <flux:text class="text-xs text-gray-600 dark:text-gray-400">{{ __('Status') }}</flux:text>
                    <flux:text class="font-medium capitalize">{{ $selectedSyncRun->status }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs text-gray-600 dark:text-gray-400">{{ __('Items Scanned') }}</flux:text>
                    <flux:text class="font-medium">{{ $selectedSyncRun->items_scanned ?? 0 }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs text-gray-600 dark:text-gray-400">{{ __('Events Created') }}</flux:text>
                    <flux:text class="font-medium">{{ $selectedSyncRun->events_created ?? 0 }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs text-gray-600 dark:text-gray-400">{{ __('Duration') }}</flux:text>
                    <flux:text class="font-medium">
                        @if ($selectedSyncRun->finished_at)
                            {{ $selectedSyncRun->started_at->diffInSeconds($selectedSyncRun->finished_at) }}s
                        @else
                            {{ __('Running') }}
                        @endif
                    </flux:text>
                </div>
            </div>

            {{-- Events List --}}
            @if ($events->isEmpty())
                <div class="p-8 text-center">
                    <flux:text class="text-gray-600 dark:text-gray-400">
                        {{ __('No events found for this sync run.') }}
                    </flux:text>
                </div>
            @else
                <div class="max-h-96 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($events as $event)
                            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <div class="flex items-start gap-3">
                                    @if ($event->event_type === 'created')
                                        <flux:icon name="plus-circle" class="w-5 h-5 text-green-500 dark:text-green-400 flex-shrink-0 mt-0.5" />
                                    @elseif ($event->event_type === 'modified')
                                        <flux:icon name="pencil" class="w-5 h-5 text-blue-500 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                                    @elseif ($event->event_type === 'deleted')
                                        <flux:icon name="trash" class="w-5 h-5 text-red-500 dark:text-red-400 flex-shrink-0 mt-0.5" />
                                    @else
                                        <flux:icon name="arrow-path" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0 mt-0.5" />
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between mb-1">
                                            <flux:text class="font-medium text-gray-900 dark:text-gray-100 capitalize">
                                                {{ $event->event_type }}
                                            </flux:text>
                                            <flux:text class="text-xs text-gray-600 dark:text-gray-400 flex-shrink-0 ml-2">
                                                {{ $event->occurred_at?->diffForHumans() }}
                                            </flux:text>
                                        </div>
                                        @if ($event->summary)
                                            <flux:text class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ $event->summary }}
                                            </flux:text>
                                        @endif
                                        @if ($event->actor_email)
                                            <flux:text class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                                {{ __('By:') }} {{ $event->actor_email }}
                                            </flux:text>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-3 text-center">
                    <flux:text class="text-xs text-gray-600 dark:text-gray-400">
                        {{ __('Showing up to 50 most recent events') }}
                    </flux:text>
                </div>
            @endif

            {{-- Error Message --}}
            @if ($selectedSyncRun->error_message)
                <div class="mt-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                    <flux:text class="text-xs font-medium text-red-800 dark:text-red-200 mb-1">
                        {{ __('Error Message:') }}
                    </flux:text>
                    <flux:text class="text-sm text-red-700 dark:text-red-300">
                        {{ $selectedSyncRun->error_message }}
                    </flux:text>
                </div>
            @endif
        @endif

        <div class="flex justify-end mt-6">
            <flux:button variant="ghost" wire:click="closeEventsModal">
                {{ __('Close') }}
            </flux:button>
        </div>
    </flux:modal>
</x-dashboard.layout>
