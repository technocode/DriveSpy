@php
    $googleAccountId = $getRecord()?->google_account_id ?? $get('../../google_account_id');
@endphp

<div
    x-data="{
        init() {
            Livewire.on('folder-selected', (event) => {
                $wire.set('root_drive_file_id', event.folderId);
                $wire.set('root_name', event.folderName);
            });
        }
    }"
    class="mb-4"
>
    <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-800">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                Browse Google Drive Folders
            </h3>
        </div>

        @livewire('google-drive-folder-picker', ['googleAccountId' => $googleAccountId], key('folder-picker-' . $googleAccountId . '-' . now()->timestamp))
    </div>
</div>
