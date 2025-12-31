<div class="space-y-4">
    @if($googleAccountId)
        <div class="space-y-3">
            {{-- Breadcrumb Navigation --}}
            <flux:breadcrumbs>
                @foreach($breadcrumbs as $index => $breadcrumb)
                    <flux:breadcrumbs.item
                        wire:click="navigateToBreadcrumb({{ $index }})"
                        class="cursor-pointer hover:text-blue-600"
                    >
                        {{ $breadcrumb['name'] }}
                    </flux:breadcrumbs.item>
                @endforeach
            </flux:breadcrumbs>

            {{-- Error Message --}}
            @if($error)
                <flux:callout variant="danger">
                    {{ $error }}
                </flux:callout>
            @endif

            {{-- Loading State --}}
            @if($loading)
                <div class="flex items-center justify-center py-8">
                    <div class="flex flex-col items-center gap-3">
                        <flux:icon.arrow-path class="size-8 animate-spin text-gray-400" />
                        <flux:text class="text-gray-600">Loading folders...</flux:text>
                    </div>
                </div>
            @else
                {{-- Folders List --}}
                <div class="border rounded-lg divide-y max-h-96 overflow-y-auto">
                    @forelse($folders as $folder)
                        <div
                            class="flex items-center justify-between p-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition"
                            wire:key="folder-{{ $folder['id'] }}"
                        >
                            <div
                                class="flex items-center gap-3 flex-1 cursor-pointer"
                                wire:click="navigateToFolder('{{ $folder['id'] }}', '{{ addslashes($folder['name']) }}')"
                            >
                                <flux:icon.folder class="size-5 text-blue-500 flex-shrink-0" />
                                <flux:text class="font-medium">{{ $folder['name'] }}</flux:text>
                            </div>

                            <flux:button
                                variant="primary"
                                size="sm"
                                wire:click="selectFolder('{{ $folder['id'] }}', '{{ addslashes($folder['name']) }}')"
                            >
                                Select
                            </flux:button>
                        </div>
                    @empty
                        <div class="p-8 text-center">
                            <flux:icon.folder-open class="size-12 text-gray-300 mx-auto mb-3" />
                            <flux:text class="text-gray-500">No folders found in this location</flux:text>
                        </div>
                    @endforelse
                </div>

                {{-- Select Current Folder --}}
                @if(count($breadcrumbs) > 0)
                    <div class="flex justify-end pt-2">
                        <flux:button
                            variant="ghost"
                            wire:click="selectFolder('{{ $currentFolderId }}', '{{ addslashes(end($breadcrumbs)['name']) }}')"
                        >
                            <flux:icon.check-circle class="size-4" />
                            Select "{{ end($breadcrumbs)['name'] }}"
                        </flux:button>
                    </div>
                @endif
            @endif
        </div>
    @else
        <flux:callout variant="warning">
            Please select a Google Account first
        </flux:callout>
    @endif
</div>
