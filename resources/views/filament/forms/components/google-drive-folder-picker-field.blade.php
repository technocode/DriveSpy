<div
    x-data="{
        googleAccountId: @entangle('data.google_account_id').live,
        pickerKey: Date.now(),
        init() {
            // Listen for folder selection
            Livewire.on('folder-selected', (event) => {
                console.log('Folder selected:', event);
                $wire.set('data.root_drive_file_id', event.folderId);
                $wire.set('data.root_name', event.folderName);
            });

            // Reload picker when account changes
            this.$watch('googleAccountId', (newValue, oldValue) => {
                if (newValue && newValue !== oldValue) {
                    this.pickerKey = Date.now();
                    console.log('Account changed, reloading picker with ID:', newValue);
                }
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

        <div x-show="googleAccountId">
            <div :key="'picker-' + googleAccountId + '-' + pickerKey">
                <div
                    x-data="{
                        mounted: false
                    }"
                    x-init="
                        $nextTick(() => {
                            if (!mounted && googleAccountId) {
                                mounted = true;
                                Livewire.dispatch('mountPicker', { accountId: googleAccountId });
                            }
                        })
                    "
                    wire:ignore
                    :id="'folder-picker-container-' + googleAccountId"
                >
                    @livewire('google-drive-folder-picker', key('folder-picker'))
                </div>
            </div>
        </div>

        <div x-show="!googleAccountId" class="text-sm text-gray-500 dark:text-gray-400 py-2">
            Please select a Google Account above to browse folders
        </div>
    </div>

    <script>
        // Update picker when account ID changes
        document.addEventListener('livewire:init', () => {
            Livewire.on('mountPicker', (data) => {
                const picker = Livewire.find('folder-picker');
                if (picker && data.accountId) {
                    picker.set('googleAccountId', data.accountId);
                }
            });
        });
    </script>
</div>
