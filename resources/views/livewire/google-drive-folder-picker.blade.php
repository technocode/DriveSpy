<div class="space-y-4">
    @if($googleAccountId)
        {{-- Breadcrumb Navigation --}}
        <div class="flex items-center gap-2 text-sm">
            @foreach($breadcrumbs as $index => $breadcrumb)
                @if($index > 0)
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                @endif
                <button
                    type="button"
                    wire:click="navigateToBreadcrumb({{ $index }})"
                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium {{ $index === count($breadcrumbs) - 1 ? 'cursor-default' : 'hover:underline' }}"
                >
                    {{ $breadcrumb['name'] }}
                </button>
            @endforeach
        </div>

        {{-- Current Folder Selection --}}
        <div class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm font-medium text-blue-900 dark:text-blue-100">
                    Select "{{ $currentFolderName }}" as monitored folder
                </span>
            </div>
            <button
                type="button"
                wire:click="selectCurrentFolder"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition"
            >
                Select This Folder
            </button>
        </div>

        {{-- Error Message --}}
        @if($error)
            <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm text-red-800 dark:text-red-200">{{ $error }}</p>
                </div>
            </div>
        @endif

        {{-- Loading State --}}
        @if($loading)
            <div class="flex flex-col items-center justify-center py-12">
                <svg class="w-10 h-10 text-blue-600 dark:text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">Loading contents...</p>
            </div>
        @else
            {{-- Folders and Files List --}}
            <div class="border border-gray-300 dark:border-gray-700 rounded-lg divide-y divide-gray-200 dark:divide-gray-700 max-h-96 overflow-y-auto bg-white dark:bg-gray-800">
                {{-- Folders Section --}}
                @if(count($folders) > 0)
                    <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-2">
                        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                            Folders ({{ count($folders) }})
                        </h4>
                    </div>
                    @foreach($folders as $folder)
                        <div
                            wire:key="folder-{{ $folder['id'] }}"
                            class="flex items-center justify-between p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition group"
                        >
                            <button
                                type="button"
                                wire:click="openFolder('{{ $folder['id'] }}', '{{ addslashes($folder['name']) }}')"
                                class="flex items-center gap-3 flex-1 text-left"
                            >
                                <svg class="w-6 h-6 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                                </svg>
                                <span class="font-medium text-gray-900 dark:text-gray-100 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                                    {{ $folder['name'] }}
                                </span>
                            </button>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    wire:click="selectFolder('{{ $folder['id'] }}', '{{ addslashes($folder['name']) }}')"
                                    class="px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 dark:text-blue-400 dark:bg-blue-900/50 dark:hover:bg-blue-900 rounded transition"
                                >
                                    Select
                                </button>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    @endforeach
                @endif

                {{-- Files Section --}}
                @if(count($files) > 0)
                    <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-2">
                        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                            Files ({{ count($files) }})
                        </h4>
                    </div>
                    @foreach($files as $file)
                        <div
                            wire:key="file-{{ $file['id'] }}"
                            class="flex items-center gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                        >
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-700 dark:text-gray-300 truncate">{{ $file['name'] }}</p>
                                @if($file['size'])
                                    <p class="text-xs text-gray-500 dark:text-gray-500">
                                        {{ $this->formatFileSize($file['size']) }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif

                {{-- Empty State --}}
                @if(count($folders) === 0 && count($files) === 0)
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400 font-medium">This folder is empty</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">No folders or files found</p>
                    </div>
                @endif
            </div>
        @endif
    @else
        <div class="p-8 text-center bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <p class="text-gray-600 dark:text-gray-400">Please select a Google Account first</p>
        </div>
    @endif
</div>

@script
<script>
    Livewire.hook('morph.updated', () => {
        console.log('Picker updated. Account ID:', $wire.googleAccountId);
    });
</script>
@endscript
