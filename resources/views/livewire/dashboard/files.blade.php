<x-dashboard.layout :heading="__('Files')" :subheading="__('Browse files from your monitored Google Drive folders')">
    {{-- Filters --}}
    <div class="mb-6 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
            {{-- Search --}}
            <div>
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    :placeholder="__('Search files and paths...')"
                    type="search"
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

            {{-- Owner Filter --}}
            <div>
                <flux:select wire:model.live="filterOwner" :placeholder="__('All owners')">
                    <option value="">{{ __('All owners') }}</option>
                    @foreach ($owners as $owner)
                        <option value="{{ $owner->owner_email }}">
                            {{ $owner->owner_name ?: $owner->owner_email }}
                        </option>
                    @endforeach
                </flux:select>
            </div>

            {{-- Modified Time Filter --}}
            <div>
                <flux:select wire:model.live="filterModifiedTime">
                    <option value="all">{{ __('Any time') }}</option>
                    <option value="today">{{ __('Today') }}</option>
                    <option value="last_7_days">{{ __('Last 7 days') }}</option>
                    <option value="last_30_days">{{ __('Last 30 days') }}</option>
                    <option value="last_90_days">{{ __('Last 90 days') }}</option>
                    <option value="last_year">{{ __('Last year') }}</option>
                </flux:select>
            </div>
        </div>

        {{-- Clear Filters --}}
        @if ($search || $filterAccountId || $filterFolderId || $filterOwner || $filterModifiedTime !== 'all')
            <div class="mt-3">
                <flux:button size="sm" variant="ghost" wire:click="clearFilters">
                    {{ __('Clear all filters') }}
                </flux:button>
            </div>
        @endif
    </div>

    {{-- Files Table --}}
    @if ($items->isEmpty())
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-12 text-center">
            <flux:icon name="document" variant="outline" class="w-16 h-16 mx-auto mb-4 text-gray-400 dark:text-gray-600" />
            <flux:heading class="mb-2">{{ __('No Files Found') }}</flux:heading>
            <flux:text class="text-gray-600 dark:text-gray-400">
                @if ($search || $filterAccountId || $filterFolderId || $filterOwner || $filterModifiedTime !== 'all')
                    {{ __('Try adjusting your filters or search query.') }}
                @else
                    {{ __('Start monitoring folders to see files here.') }}
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
                                    {{ __('Name / Path') }}
                                </flux:text>
                            </th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">
                                <flux:text class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                                    {{ __('Account') }}
                                </flux:text>
                            </th>
                            <th class="px-4 py-3 text-left hidden lg:table-cell">
                                <flux:text class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                                    {{ __('Size') }}
                                </flux:text>
                            </th>
                            <th class="px-4 py-3 text-left hidden lg:table-cell">
                                <flux:text class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                                    {{ __('Owner') }}
                                </flux:text>
                            </th>
                            <th class="px-4 py-3 text-left hidden lg:table-cell">
                                <flux:text class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                                    {{ __('Modified') }}
                                </flux:text>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($items as $item)
                            @php
                                // Extract directory path without filename
                                $directoryPath = $item->path_cache ? dirname($item->path_cache) : '/';
                                $directoryPath = $directoryPath === '.' ? '/' : $directoryPath;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <flux:icon name="document" class="w-5 h-5 text-gray-400 flex-shrink-0" />
                                        <div class="flex-1 min-w-0">
                                            <flux:text class="font-medium text-gray-900 dark:text-gray-100 truncate">
                                                {{ $item->name }}
                                            </flux:text>
                                            <flux:text class="text-xs text-gray-500 dark:text-gray-400 font-mono truncate block" title="{{ $directoryPath }}">
                                                {{ $directoryPath }}
                                            </flux:text>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 hidden md:table-cell">
                                    <flux:text class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                        {{ $item->googleAccount->email }}
                                    </flux:text>
                                </td>
                                <td class="px-4 py-3 hidden lg:table-cell">
                                    <flux:text class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $this->formatFileSize($item->size_bytes) }}
                                    </flux:text>
                                </td>
                                <td class="px-4 py-3 hidden lg:table-cell">
                                    <div>
                                        @if ($item->owner_name || $item->owner_email)
                                            <flux:text class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ $item->owner_name ?? $item->owner_email }}
                                            </flux:text>
                                            @if ($item->last_modifier_name || $item->last_modifier_email)
                                                <flux:text class="text-xs text-gray-500 dark:text-gray-400 block">
                                                    {{ __('Modified by:') }} {{ $item->last_modifier_name ?? $item->last_modifier_email }}
                                                </flux:text>
                                            @endif
                                        @else
                                            <flux:text class="text-sm text-gray-400">—</flux:text>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 hidden lg:table-cell">
                                    <flux:text class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $item->modified_time?->diffForHumans() ?? '—' }}
                                    </flux:text>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $items->links() }}
            </div>
        </div>

        {{-- Results count --}}
        <div class="mt-4 text-center">
            <flux:text class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('Showing :from to :to of :total results', [
                    'from' => $items->firstItem() ?? 0,
                    'to' => $items->lastItem() ?? 0,
                    'total' => $items->total(),
                ]) }}
            </flux:text>
        </div>
    @endif
</x-dashboard.layout>
