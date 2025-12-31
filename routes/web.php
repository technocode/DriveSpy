<?php

use App\Http\Controllers\GoogleOAuthController;
use App\Livewire\Dashboard\Activity;
use App\Livewire\Dashboard\Dashboard;
use App\Livewire\Dashboard\Documentation;
use App\Livewire\Dashboard\Files;
use App\Livewire\Dashboard\GoogleAccounts;
use App\Livewire\Dashboard\MonitoredFolders;
use App\Livewire\Dashboard\SyncHistory;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/terms', function () {
    return view('terms');
})->name('terms');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('google-accounts', GoogleAccounts::class)->name('google-accounts');
        Route::get('monitored-folders', MonitoredFolders::class)->name('monitored-folders');
        Route::get('files', Files::class)->name('files');
        Route::get('sync-history', SyncHistory::class)->name('sync-history');
        Route::get('activity', Activity::class)->name('activity');
        Route::get('documentation', Documentation::class)->name('documentation');
    });

    Route::redirect('settings', 'dashboard/settings/profile');
    Route::redirect('dashboard/settings', 'dashboard/settings/profile');

    Route::prefix('dashboard/settings')->name('settings.')->group(function () {
        Route::get('profile', Profile::class)->name('profile');
        Route::get('password', Password::class)->name('password');
        Route::get('appearance', Appearance::class)->name('appearance');

        Route::get('two-factor', TwoFactor::class)
            ->middleware(
                when(
                    Features::canManageTwoFactorAuthentication()
                        && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                    ['password.confirm'],
                    [],
                ),
            )
            ->name('two-factor');
    });

    Route::get('auth/google', [GoogleOAuthController::class, 'redirect'])->name('google.oauth.redirect');
    Route::get('auth/google/callback', [GoogleOAuthController::class, 'callback'])->name('google.oauth.callback');
});
