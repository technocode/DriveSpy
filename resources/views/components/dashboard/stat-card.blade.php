@props(['label', 'value', 'icon' => null])

<div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6">
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <flux:text class="text-sm font-medium text-gray-600 dark:text-gray-400">
                {{ $label }}
            </flux:text>
            <flux:heading class="mt-2 text-3xl font-bold">
                {{ $value }}
            </flux:heading>
        </div>
        @if ($icon)
            <div class="flex-shrink-0">
                <flux:icon :name="$icon" variant="outline" class="w-12 h-12 text-gray-400 dark:text-gray-600" />
            </div>
        @endif
    </div>
</div>
