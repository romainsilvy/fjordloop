<?php

use App\Http\Controllers\InvitationsController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Travel\Index as TravelIndex;
use App\Livewire\Travel\Show as TravelShow;
use App\Livewire\Activity\Show as ActivityShow;

use Illuminate\Support\Facades\Route;

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    Route::prefix('travel')
        ->name('travel.')
        ->group(function () {
            Route::get('/', TravelIndex::class)->name('index');
            Route::get('/{travelId}', TravelShow::class)->name('show');

            Route::prefix('{travelId}/activity')
                ->name('activity.')
                ->group(function () {
                    Route::get('/{activityId}', ActivityShow::class)->name('show');
                });

            Route::prefix('{token}')->name('invitation.')->controller(InvitationsController::class)->group(function () {
                Route::get('accept', 'accept')->name('accept');
                Route::get('refuse', 'refuse')->name('refuse');
            });
        });
});

require __DIR__ . '/auth.php';
