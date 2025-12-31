<x-dashboard.layout :heading="__('Monitored Folders')" :subheading="__('Configure which Google Drive folders to monitor')">
    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="mb-6 rounded-lg border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-4">
            <flux:text class="text-green-800 dark:text-green-200">
                {{ session('success') }}
            </flux:text>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-4">
            <flux:text class="text-red-800 dark:text-red-200">
                {{ session('error') }}
            </flux:text>
        </div>
    @endif

    {{-- Add Folder Button --}}
    <div class="mb-6">
        <flux:button variant="primary" wire:click="openCreateModal" icon="plus">
            {{ __('Monitor New Folder') }}
        </flux:button>
    </div>

    {{-- Folders List --}}
    @if ($folders->isEmpty())
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-12 text-center">
            <flux:icon name="folder" variant="outline" class="w-16 h-16 mx-auto mb-4 text-gray-400 dark:text-gray-600" />
            <flux:heading class="mb-2">{{ __('No Monitored Folders') }}</flux:heading>
            <flux:text class="text-gray-600 dark:text-gray-400 mb-4">
                {{ __('Start monitoring a Google Drive folder to track changes and sync files.') }}
            </flux:text>
            <flux:button variant="primary" wire:click="openCreateModal">
                {{ __('Monitor Your First Folder') }}
            </flux:button>
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            @foreach ($folders as $folder)
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            <flux:icon name="folder" class="w-8 h-8 text-blue-500 dark:text-blue-400 flex-shrink-0" />
                            <div class="flex-1 min-w-0">
                                <flux:heading class="mb-1 truncate">
                                    {{ $folder->root_name }}
                                </flux:heading>
                                <flux:text class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                    {{ $folder->googleAccount->email }}
                                </flux:text>
                            </div>
                        </div>
                        @if ($folder->status === 'active')
                            <flux:badge variant="success">{{ __('Active') }}</flux:badge>
                        @else
                            <flux:badge variant="danger">{{ $folder->status }}</flux:badge>
                        @endif
                    </div>

                    {{-- Settings --}}
                    <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-900/50 rounded">
                        <flux:text class="text-xs text-gray-600 dark:text-gray-400">
                            {{ __('Include subfolders:') }}
                            <strong class="text-gray-900 dark:text-gray-100">
                                {{ $folder->include_subfolders ? __('Yes') : __('No') }}
                            </strong>
                        </flux:text>
                    </div>

                    {{-- Stats --}}
                    <div class="mb-4 grid grid-cols-2 gap-4">
                        <div>
                            <flux:text class="text-xs text-gray-600 dark:text-gray-400">
                                {{ __('Items') }}
                            </flux:text>
                            <flux:heading class="text-lg">
                                {{ $folder->drive_items_count }}
                            </flux:heading>
                        </div>
                        <div>
                            <flux:text class="text-xs text-gray-600 dark:text-gray-400">
                                {{ __('Syncs') }}
                            </flux:text>
                            <flux:heading class="text-lg">
                                {{ $folder->sync_runs_count }}
                            </flux:heading>
                        </div>
                    </div>

                    {{-- Last Indexed --}}
                    @if ($folder->last_indexed_at)
                        <div class="mb-4">
                            <flux:text class="text-xs text-gray-600 dark:text-gray-400">
                                {{ __('Last indexed') }} {{ $folder->last_indexed_at->diffForHumans() }}
                            </flux:text>
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="flex gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                        @if ($folder->last_indexed_at === null && $folder->status !== 'indexing')
                            <flux:button
                                variant="primary"
                                size="sm"
                                wire:click="startInitialIndex({{ $folder->id }})"
                                wire:confirm="This will scan all files in this folder. This may take a while for large folders. Continue?"
                                class="flex-1"
                            >
                                <flux:icon name="arrow-path" class="w-4 h-4 mr-2" />
                                {{ __('Start Indexing') }}
                            </flux:button>
                        @elseif ($folder->status === 'indexing')
                            <div class="flex-1 flex items-center justify-center text-sm text-blue-600 dark:text-blue-400">
                                <flux:icon name="arrow-path" class="w-4 h-4 mr-2 animate-spin" />
                                {{ __('Indexing in progress...') }}
                            </div>
                        @else
                            <flux:dropdown position="top" align="start">
                                <flux:button
                                    variant="primary"
                                    size="sm"
                                    icon="arrow-path"
                                    icon-trailing="chevron-down"
                                >
                                    {{ __('Actions') }}
                                </flux:button>

                                <flux:menu class="!w-[180px]">
                                    <flux:menu.radio.group>
                                        <flux:menu.item 
                                            as="button" 
                                            wire:click="syncFolder({{ $folder->id }})"
                                            icon="arrow-path"
                                        >
                                            {{ __('Quick Sync') }}
                                        </flux:menu.item>
                                        <flux:menu.item 
                                            as="button" 
                                            wire:click="fullSyncFolder({{ $folder->id }})"
                                            wire:confirm="This will scan all files recursively in this folder. This may take a while for large folders. Continue?"
                                            icon="magnifying-glass"
                                        >
                                            {{ __('Full Sync') }}
                                        </flux:menu.item>
                                        <flux:menu.item 
                                            as="button" 
                                            wire:click="openEditModal({{ $folder->id }})"
                                            icon="cog"
                                        >
                                            {{ __('Edit Settings') }}
                                        </flux:menu.item>
                                    </flux:menu.radio.group>
                                    <flux:menu.separator />
                                    <flux:menu.radio.group>
                                        <flux:menu.item 
                                            as="button" 
                                            wire:click="delete({{ $folder->id }})"
                                            wire:confirm="Are you sure? This will stop monitoring this folder and remove all tracked data."
                                            icon="trash"
                                            class="!text-red-600 dark:!text-red-400"
                                        >
                                            {{ __('Stop Monitoring') }}
                                        </flux:menu.item>
                                    </flux:menu.radio.group>
                                </flux:menu>
                            </flux:dropdown>
                        @endif
                        @if ($folder->last_indexed_at === null || $folder->status === 'indexing')
                            <flux:button
                                variant="danger"
                                size="sm"
                                wire:click="delete({{ $folder->id }})"
                                wire:confirm="Are you sure? This will stop monitoring this folder and remove all tracked data."
                                class="flex-1"
                            >
                                {{ __('Stop Monitoring') }}
                            </flux:button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $folders->links() }}
        </div>
    @endif

    {{-- Create Modal --}}
    <flux:modal :open="$showCreateModal" wire:model="showCreateModal">
        <flux:heading class="mb-4">{{ __('Monitor New Folder') }}</flux:heading>

        <div class="space-y-4">
            {{-- Account Selection --}}
            <div>
                <flux:select wire:model.live="selectedAccountId" :label="__('Google Account')" required>
                    <option value="">{{ __('Select an account') }}</option>
                    @foreach ($googleAccounts as $account)
                        <option value="{{ $account->id }}">{{ $account->email }}</option>
                    @endforeach
                </flux:select>
            </div>

            {{-- Selected Folder Display --}}
            @if ($selectedFolderId)
                <div class="p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                    <div class="flex items-center gap-2">
                        <flux:icon name="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400" />
                        <flux:text class="text-sm font-medium text-green-900 dark:text-green-100">
                            {{ __('Selected:') }} {{ $selectedFolderName }}
                        </flux:text>
                    </div>
                </div>
            @endif

            {{-- Folder Picker --}}
            @if ($selectedAccountId)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-900/50">
                    <flux:text class="text-sm font-medium mb-3">{{ __('Browse and select a folder:') }}</flux:text>
                    <livewire:google-drive-folder-picker :key="$selectedAccountId" :googleAccountId="$selectedAccountId" />
                </div>
            @endif

            {{-- Include Subfolders Toggle --}}
            <div>
                <label class="flex items-center gap-3">
                    <flux:switch wire:model="includeSubfolders" />
                    <div>
                        <flux:text class="font-medium">{{ __('Include subfolders') }}</flux:text>
                        <flux:text class="text-xs text-gray-600 dark:text-gray-400">
                            {{ __('Monitor all subfolders within the selected folder') }}
                        </flux:text>
                    </div>
                </label>
            </div>
        </div>

        <div class="flex gap-2 mt-6">
            <flux:button variant="primary" wire:click="create" :disabled="!$selectedFolderId">
                {{ __('Start Monitoring') }}
            </flux:button>
            <flux:button variant="ghost" wire:click="closeCreateModal">
                {{ __('Cancel') }}
            </flux:button>
        </div>
    </flux:modal>

    {{-- Edit Modal --}}
    <flux:modal :open="$showEditModal" wire:model="showEditModal">
        <flux:heading class="mb-4">{{ __('Edit Folder Settings') }}</flux:heading>

        <div class="space-y-4">
            <div>
                <label class="flex items-center gap-3">
                    <flux:switch wire:model="includeSubfolders" />
                    <div>
                        <flux:text class="font-medium">{{ __('Include subfolders') }}</flux:text>
                        <flux:text class="text-xs text-gray-600 dark:text-gray-400">
                            {{ __('Monitor all subfolders within the selected folder') }}
                        </flux:text>
                    </div>
                </label>
            </div>
        </div>

        <div class="flex gap-2 mt-6">
            <flux:button variant="primary" wire:click="update">
                {{ __('Save Changes') }}
            </flux:button>
            <flux:button variant="ghost" wire:click="closeEditModal">
                {{ __('Cancel') }}
            </flux:button>
        </div>
    </flux:modal>
</x-dashboard.layout>
