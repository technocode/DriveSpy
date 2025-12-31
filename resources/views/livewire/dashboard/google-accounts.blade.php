<x-dashboard.layout :heading="__('Google Accounts')" :subheading="__('Manage your connected Google accounts')">
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

    {{-- Connect New Account Button --}}
    <div class="mb-6">
        <flux:button variant="primary" href="{{ route('google.oauth.redirect') }}">
            <flux:icon name="plus" class="w-4 h-4 mr-2" />
            {{ __('Connect New Account') }}
        </flux:button>
    </div>

    {{-- Accounts List --}}
    @if ($accounts->isEmpty())
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-12 text-center">
            <flux:icon name="user-group" variant="outline" class="w-16 h-16 mx-auto mb-4 text-gray-400 dark:text-gray-600" />
            <flux:heading class="mb-2">{{ __('No Google Accounts Connected') }}</flux:heading>
            <flux:text class="text-gray-600 dark:text-gray-400 mb-4">
                {{ __('Connect a Google account to start monitoring your Google Drive folders.') }}
            </flux:text>
            <flux:button variant="primary" href="{{ route('google.oauth.redirect') }}">
                {{ __('Connect Your First Account') }}
            </flux:button>
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($accounts as $account)
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6">
                    <div class="flex items-start gap-4">
                        {{-- Avatar --}}
                        @if ($account->avatar_url)
                            <img src="{{ $account->avatar_url }}" alt="{{ $account->display_name }}" class="w-12 h-12 rounded-full flex-shrink-0">
                        @else
                            <div class="w-12 h-12 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                                <flux:icon name="user" class="w-6 h-6 text-gray-500 dark:text-gray-400" />
                            </div>
                        @endif

                        {{-- Account Info --}}
                        <div class="flex-1 min-w-0">
                            <flux:heading class="mb-1 truncate">
                                {{ $account->display_name }}
                            </flux:heading>
                            <flux:text class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                {{ $account->email }}
                            </flux:text>
                        </div>
                    </div>

                    {{-- Status Badge --}}
                    <div class="mt-4">
                        @if ($account->status === 'active')
                            <flux:badge variant="success">{{ __('Active') }}</flux:badge>
                        @else
                            <flux:badge variant="danger">{{ $account->status }}</flux:badge>
                        @endif
                    </div>

                    {{-- Stats --}}
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div>
                            <flux:text class="text-xs text-gray-600 dark:text-gray-400">
                                {{ __('Folders') }}
                            </flux:text>
                            <flux:heading class="text-lg">
                                {{ $account->monitored_folders_count }}
                            </flux:heading>
                        </div>
                        <div>
                            <flux:text class="text-xs text-gray-600 dark:text-gray-400">
                                {{ __('Syncs') }}
                            </flux:text>
                            <flux:heading class="text-lg">
                                {{ $account->sync_runs_count }}
                            </flux:heading>
                        </div>
                    </div>

                    {{-- Last Synced --}}
                    @if ($account->last_synced_at)
                        <div class="mt-4">
                            <flux:text class="text-xs text-gray-600 dark:text-gray-400">
                                {{ __('Last synced') }} {{ $account->last_synced_at->diffForHumans() }}
                            </flux:text>
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <flux:button
                            variant="danger"
                            size="sm"
                            wire:click="disconnect({{ $account->id }})"
                            wire:confirm="Are you sure you want to disconnect this account? All monitored folders and sync data will be removed."
                            class="w-full"
                        >
                            {{ __('Disconnect') }}
                        </flux:button>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $accounts->links() }}
        </div>
    @endif
</x-dashboard.layout>
