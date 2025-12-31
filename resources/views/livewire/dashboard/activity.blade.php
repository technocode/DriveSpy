<x-dashboard.layout :heading="__('Activity Log')" :subheading="__('Track all file changes and activities in your monitored folders')">
    {{-- Filters --}}
    <div class="mb-6 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            {{-- Search --}}
            <div class="md:col-span-2">
                <flux:input 
                    wire:model.live.debounce.300ms="search" 
                    :placeholder="__('Search by file name, ID, or actor...')"
                    icon="magnifying-glass"
                />
            </div>

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
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 mt-4">
            {{-- Event Type Filter --}}
            <div>
                <flux:select wire:model.live="filterEventType">
                    <option value="all">{{ __('All event types') }}</option>
                    @foreach ($eventTypes as $value => $label)
                        @if ($value !== 'all')
                            <option value="{{ $value }}">{{ __($label) }}</option>
                        @endif
                    @endforeach
                </flux:select>
            </div>

            {{-- Clear Filters --}}
            @if ($filterAccountId || $filterFolderId || $filterEventType !== 'all' || $search)
                <div class="flex items-end">
                    <flux:button size="sm" variant="ghost" wire:click="clearFilters">
                        {{ __('Clear all filters') }}
                    </flux:button>
                </div>
            @endif
        </div>
    </div>

    {{-- Events List --}}
    @if ($events->isEmpty())
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-12 text-center">
            <flux:icon name="clock" variant="outline" class="w-16 h-16 mx-auto mb-4 text-gray-400 dark:text-gray-600" />
            <flux:heading class="mb-2">{{ __('No Activity Found') }}</flux:heading>
            <flux:text class="text-gray-600 dark:text-gray-400">
                @if ($filterAccountId || $filterFolderId || $filterEventType !== 'all' || $search)
                    {{ __('Try adjusting your filters or run a sync to detect changes.') }}
                @else
                    {{ __('Activity will appear here once files are changed in your monitored folders. Run a sync to detect recent changes.') }}
                @endif
            </flux:text>
        </div>
    @else
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($events as $event)
                    <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <div class="flex items-start gap-4">
                            {{-- Event Icon --}}
                            <div class="flex-shrink-0">
                                @if ($event->event_type === 'created')
                                    <flux:icon name="plus-circle" class="w-6 h-6 text-green-500 dark:text-green-400" />
                                @elseif ($event->event_type === 'updated')
                                    <flux:icon name="pencil" class="w-6 h-6 text-blue-500 dark:text-blue-400" />
                                @elseif ($event->event_type === 'deleted')
                                    <flux:icon name="trash" class="w-6 h-6 text-red-500 dark:text-red-400" />
                                @elseif ($event->event_type === 'trashed')
                                    <flux:icon name="archive-box" class="w-6 h-6 text-orange-500 dark:text-orange-400" />
                                @elseif ($event->event_type === 'restored')
                                    <flux:icon name="arrow-uturn-left" class="w-6 h-6 text-green-500 dark:text-green-400" />
                                @elseif ($event->event_type === 'renamed')
                                    <flux:icon name="pencil-square" class="w-6 h-6 text-yellow-500 dark:text-yellow-400" />
                                @elseif ($event->event_type === 'moved')
                                    <flux:icon name="arrow-right-circle" class="w-6 h-6 text-purple-500 dark:text-purple-400" />
                                @else
                                    <flux:icon name="information-circle" class="w-6 h-6 text-gray-500 dark:text-gray-400" />
                                @endif
                            </div>

                            {{-- Event Details --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4 mb-2">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <flux:badge 
                                                :color="match($event->event_type) {
                                                    'created' => 'green',
                                                    'updated' => 'blue',
                                                    'deleted' => 'red',
                                                    'trashed' => 'orange',
                                                    'restored' => 'green',
                                                    'renamed' => 'yellow',
                                                    'moved' => 'purple',
                                                    default => 'gray',
                                                }"
                                                size="sm"
                                            >
                                                {{ ucfirst(str_replace('_', ' ', $event->event_type)) }}
                                            </flux:badge>
                                            @if ($event->monitoredFolder)
                                                <flux:text class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $event->monitoredFolder->root_name }}
                                                </flux:text>
                                            @endif
                                        </div>
                                        @if ($event->summary)
                                            <flux:text class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $event->summary }}
                                            </flux:text>
                                        @else
                                            <flux:text class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ __('File ID:') }} {{ \Illuminate\Support\Str::limit($event->drive_file_id, 30) }}
                                            </flux:text>
                                        @endif
                                    </div>
                                    <flux:text class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0">
                                        {{ $event->updated_at->diffForHumans() }}
                                    </flux:text>
                                </div>

                                <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                    @if ($event->actor_email)
                                        <span class="font-medium text-gray-700 dark:text-gray-300">
                                            <flux:icon name="user" class="w-3 h-3 inline mr-1" />
                                            {{ __('By:') }} {{ $event->actor_name ?? $event->actor_email }}
                                        </span>
                                    @endif
                                    @if ($event->googleAccount)
                                        <span>{{ __('Account:') }} {{ $event->googleAccount->email }}</span>
                                    @endif
                                    <span>{{ __('Source:') }} {{ $event->change_source }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $events->links() }}
            </div>
        </div>

        {{-- Results count --}}
        <div class="mt-4 text-center">
            <flux:text class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('Showing :from to :to of :total results', [
                    'from' => $events->firstItem() ?? 0,
                    'to' => $events->lastItem() ?? 0,
                    'total' => $events->total(),
                ]) }}
            </flux:text>
        </div>
    @endif
</x-dashboard.layout>

