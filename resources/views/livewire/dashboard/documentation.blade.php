<x-dashboard.layout :heading="__('Documentation')" :subheading="__('Learn how to use DriveSpy to monitor your Google Drive')">
    <div class="space-y-8">
        {{-- Getting Started Section --}}
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
            <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <flux:heading size="lg">{{ __('Getting Started') }}</flux:heading>
            </div>
            <div class="px-6 py-6 space-y-4">
                <div>
                    <flux:heading size="base" class="mb-2">{{ __('What is DriveSpy?') }}</flux:heading>
                    <flux:text class="text-gray-700 dark:text-gray-300">
                        {{ __('DriveSpy is a comprehensive Google Drive monitoring application that helps you track changes, manage multiple Google accounts, and monitor specific folders for file activities. It provides real-time synchronization and detailed activity logs for all your monitored content.') }}
                    </flux:text>
                </div>

                <div class="pt-4">
                    <flux:heading size="base" class="mb-2">{{ __('Key Features') }}</flux:heading>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                        <li>{{ __('Monitor multiple Google Drive accounts simultaneously') }}</li>
                        <li>{{ __('Track changes in specific folders') }}</li>
                        <li>{{ __('View detailed sync history and activity logs') }}</li>
                        <li>{{ __('Real-time file change notifications') }}</li>
                        <li>{{ __('Automatic synchronization with configurable schedules') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Connecting Google Accounts --}}
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
            <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <flux:heading size="lg">{{ __('Connecting Google Accounts') }}</flux:heading>
            </div>
            <div class="px-6 py-6 space-y-4">
                <div>
                    <flux:heading size="base" class="mb-2">{{ __('How to Connect') }}</flux:heading>
                    <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                        <li>{{ __('Navigate to the Google Accounts page from the sidebar menu') }}</li>
                        <li>{{ __('Click the "Connect Google Account" button') }}</li>
                        <li>{{ __('Sign in with your Google account when prompted') }}</li>
                        <li>{{ __('Grant the necessary permissions to access your Google Drive') }}</li>
                        <li>{{ __('Your account will be added to the list and is ready to be monitored') }}</li>
                    </ol>
                </div>

                <div class="pt-4">
                    <flux:callout variant="note" icon="information-circle">
                        {{ __('You can connect multiple Google accounts to monitor different drives or separate personal and work accounts.') }}
                    </flux:callout>
                </div>
            </div>
        </div>

        {{-- Setting Up Monitored Folders --}}
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
            <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <flux:heading size="lg">{{ __('Setting Up Monitored Folders') }}</flux:heading>
            </div>
            <div class="px-6 py-6 space-y-4">
                <div>
                    <flux:heading size="base" class="mb-2">{{ __('Creating a Monitored Folder') }}</flux:heading>
                    <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                        <li>{{ __('Go to the Monitored Folders page') }}</li>
                        <li>{{ __('Click "Add Monitored Folder"') }}</li>
                        <li>{{ __('Select the Google account you want to monitor') }}</li>
                        <li>{{ __('Choose the folder you want to track using the folder picker') }}</li>
                        <li>{{ __('Configure the sync schedule (hourly, daily, weekly, or manual)') }}</li>
                        <li>{{ __('Save your settings') }}</li>
                    </ol>
                </div>

                <div class="pt-4">
                    <flux:heading size="base" class="mb-2">{{ __('Sync Schedule Options') }}</flux:heading>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                        <div class="p-4 rounded border border-gray-200 dark:border-gray-600 space-y-1">
                            <flux:text class="font-semibold text-gray-900 dark:text-gray-100 block">{{ __('Hourly') }}</flux:text>
                            <flux:text class="text-sm text-gray-600 dark:text-gray-400 block">{{ __('Syncs every hour automatically') }}</flux:text>
                        </div>
                        <div class="p-4 rounded border border-gray-200 dark:border-gray-600 space-y-1">
                            <flux:text class="font-semibold text-gray-900 dark:text-gray-100 block">{{ __('Daily') }}</flux:text>
                            <flux:text class="text-sm text-gray-600 dark:text-gray-400 block">{{ __('Syncs once per day at midnight') }}</flux:text>
                        </div>
                        <div class="p-4 rounded border border-gray-200 dark:border-gray-600 space-y-1">
                            <flux:text class="font-semibold text-gray-900 dark:text-gray-100 block">{{ __('Weekly') }}</flux:text>
                            <flux:text class="text-sm text-gray-600 dark:text-gray-400 block">{{ __('Syncs once per week') }}</flux:text>
                        </div>
                        <div class="p-4 rounded border border-gray-200 dark:border-gray-600 space-y-1">
                            <flux:text class="font-semibold text-gray-900 dark:text-gray-100 block">{{ __('Manual') }}</flux:text>
                            <flux:text class="text-sm text-gray-600 dark:text-gray-400 block">{{ __('Only syncs when you trigger it manually') }}</flux:text>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Understanding File Activities --}}
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
            <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <flux:heading size="lg">{{ __('Understanding File Activities') }}</flux:heading>
            </div>
            <div class="px-6 py-6 space-y-4">
                <div>
                    <flux:heading size="base" class="mb-2">{{ __('Event Types') }}</flux:heading>
                    <flux:text class="text-gray-700 dark:text-gray-300 mb-4">
                        {{ __('DriveSpy tracks various types of file activities:') }}
                    </flux:text>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <flux:icon name="plus-circle" class="w-5 h-5 text-green-500 dark:text-green-400 flex-shrink-0 mt-0.5" />
                            <div class="space-y-1 flex-1">
                                <flux:text class="font-semibold text-gray-900 dark:text-gray-100 block">{{ __('Created') }}</flux:text>
                                <flux:text class="text-sm text-gray-600 dark:text-gray-400 block">{{ __('A new file or folder was added') }}</flux:text>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <flux:icon name="pencil" class="w-5 h-5 text-blue-500 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                            <div class="space-y-1 flex-1">
                                <flux:text class="font-semibold text-gray-900 dark:text-gray-100 block">{{ __('Updated') }}</flux:text>
                                <flux:text class="text-sm text-gray-600 dark:text-gray-400 block">{{ __('File content or metadata was modified') }}</flux:text>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <flux:icon name="pencil-square" class="w-5 h-5 text-yellow-500 dark:text-yellow-400 flex-shrink-0 mt-0.5" />
                            <div class="space-y-1 flex-1">
                                <flux:text class="font-semibold text-gray-900 dark:text-gray-100 block">{{ __('Renamed') }}</flux:text>
                                <flux:text class="text-sm text-gray-600 dark:text-gray-400 block">{{ __('File or folder name was changed') }}</flux:text>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <flux:icon name="arrow-right-circle" class="w-5 h-5 text-purple-500 dark:text-purple-400 flex-shrink-0 mt-0.5" />
                            <div class="space-y-1 flex-1">
                                <flux:text class="font-semibold text-gray-900 dark:text-gray-100 block">{{ __('Moved') }}</flux:text>
                                <flux:text class="text-sm text-gray-600 dark:text-gray-400 block">{{ __('File or folder was moved to a different location') }}</flux:text>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <flux:icon name="archive-box" class="w-5 h-5 text-orange-500 dark:text-orange-400 flex-shrink-0 mt-0.5" />
                            <div class="space-y-1 flex-1">
                                <flux:text class="font-semibold text-gray-900 dark:text-gray-100 block">{{ __('Trashed') }}</flux:text>
                                <flux:text class="text-sm text-gray-600 dark:text-gray-400 block">{{ __('File or folder was moved to trash') }}</flux:text>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <flux:icon name="arrow-uturn-left" class="w-5 h-5 text-green-500 dark:text-green-400 flex-shrink-0 mt-0.5" />
                            <div class="space-y-1 flex-1">
                                <flux:text class="font-semibold text-gray-900 dark:text-gray-100 block">{{ __('Restored') }}</flux:text>
                                <flux:text class="text-sm text-gray-600 dark:text-gray-400 block">{{ __('File or folder was restored from trash') }}</flux:text>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <flux:icon name="trash" class="w-5 h-5 text-red-500 dark:text-red-400 flex-shrink-0 mt-0.5" />
                            <div class="space-y-1 flex-1">
                                <flux:text class="font-semibold text-gray-900 dark:text-gray-100 block">{{ __('Deleted') }}</flux:text>
                                <flux:text class="text-sm text-gray-600 dark:text-gray-400 block">{{ __('File or folder was permanently deleted') }}</flux:text>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Using the Dashboard --}}
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
            <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <flux:heading size="lg">{{ __('Using the Dashboard') }}</flux:heading>
            </div>
            <div class="px-6 py-6 space-y-4">
                <div>
                    <flux:heading size="base" class="mb-2">{{ __('Overview') }}</flux:heading>
                    <flux:text class="text-gray-700 dark:text-gray-300">
                        {{ __('The dashboard provides a quick overview of your monitoring status with:') }}
                    </flux:text>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 mt-2 ml-2">
                        <li>{{ __('Total connected Google accounts') }}</li>
                        <li>{{ __('Number of monitored folders') }}</li>
                        <li>{{ __('Total files being tracked') }}</li>
                        <li>{{ __('Recent synchronization activity') }}</li>
                        <li>{{ __('Latest file change events') }}</li>
                    </ul>
                </div>

                <div class="pt-4">
                    <flux:heading size="base" class="mb-2">{{ __('Navigation') }}</flux:heading>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-2">
                        <li><strong>{{ __('Overview:') }}</strong> {{ __('Dashboard with statistics and recent activity') }}</li>
                        <li><strong>{{ __('Google Accounts:') }}</strong> {{ __('Manage your connected Google accounts') }}</li>
                        <li><strong>{{ __('Monitored Folders:') }}</strong> {{ __('Configure which folders to track') }}</li>
                        <li><strong>{{ __('Files:') }}</strong> {{ __('Browse all tracked files across your accounts') }}</li>
                        <li><strong>{{ __('Sync History:') }}</strong> {{ __('View past synchronization runs') }}</li>
                        <li><strong>{{ __('Activity Log:') }}</strong> {{ __('Detailed log of all file changes') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Troubleshooting --}}
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
            <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <flux:heading size="lg">{{ __('Troubleshooting') }}</flux:heading>
            </div>
            <div class="px-6 py-6 space-y-4">
                <div>
                    <flux:heading size="base" class="mb-2">{{ __('Common Issues') }}</flux:heading>

                    <div class="space-y-4 mt-4">
                        <div class="space-y-1">
                            <flux:text class="font-semibold text-gray-900 dark:text-gray-100 block">{{ __('Sync not running automatically') }}</flux:text>
                            <flux:text class="text-sm text-gray-600 dark:text-gray-400 block">
                                {{ __('Ensure the Laravel scheduler is running. The cron job must be configured on your server to run scheduled tasks.') }}
                            </flux:text>
                        </div>

                        <div class="space-y-1">
                            <flux:text class="font-semibold text-gray-900 dark:text-gray-100 block">{{ __('Unable to connect Google account') }}</flux:text>
                            <flux:text class="text-sm text-gray-600 dark:text-gray-400 block">
                                {{ __('Check that your Google API credentials are properly configured and that the OAuth consent screen is set up correctly.') }}
                            </flux:text>
                        </div>

                        <div class="space-y-1">
                            <flux:text class="font-semibold text-gray-900 dark:text-gray-100 block">{{ __('Missing file changes') }}</flux:text>
                            <flux:text class="text-sm text-gray-600 dark:text-gray-400 block">
                                {{ __('Try running a manual sync from the Monitored Folders page. If the issue persists, check the Sync History for any errors.') }}
                            </flux:text>
                        </div>

                        <div class="space-y-1">
                            <flux:text class="font-semibold text-gray-900 dark:text-gray-100 block">{{ __('Folder picker not loading') }}</flux:text>
                            <flux:text class="text-sm text-gray-600 dark:text-gray-400 block">
                                {{ __('Ensure the Google account is properly connected and has access to Google Drive. Try disconnecting and reconnecting the account if needed.') }}
                            </flux:text>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Need Help --}}
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
            <div class="px-6 py-6 text-center">
                <flux:icon name="question-mark-circle" variant="outline" class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-4" />
                <flux:heading size="lg" class="mb-2">{{ __('Need More Help?') }}</flux:heading>
                <flux:text class="text-gray-600 dark:text-gray-400">
                    {{ __('If you have questions or need assistance, please contact your system administrator.') }}
                </flux:text>
            </div>
        </div>
    </div>
</x-dashboard.layout>
