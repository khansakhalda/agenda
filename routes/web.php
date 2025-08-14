<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Livewire\Settings\Tasks;

Route::get('/', App\Livewire\CalendarAdmin::class)->name('calendar.admin');
Route::get('/user', App\Livewire\CalendarUser::class)->name('calendar.user');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/**
 * PUBLIC: Tasks bisa diakses tanpa login
 */
Route::get('settings/tasks', Tasks::class)->name('settings.tasks');

/**
 * PRIVATE: Route yang tetap butuh login
 */
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__ . '/auth.php';
