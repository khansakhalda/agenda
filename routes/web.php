<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

use App\Livewire\CalendarAdmin;
use App\Livewire\CalendarUser;
use App\Livewire\Settings\Tasks;

// Root -> login
Route::redirect('/', '/login');

// (opsional) dashboard
Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // Halaman utama setelah login
    Route::get('/settings/calendar', CalendarAdmin::class)->name('calendar.admin');

    // (opsional)
    Route::get('/settings/calendar-user', CalendarUser::class)->name('calendar.user');

    // Tasks (path lama tetap ada)
    Route::get('/settings/tasks', Tasks::class)->name('settings.tasks');

    // Alias langsung /tasks
    Route::get('/tasks', Tasks::class)->name('tasks');

    // Settings lain via Volt
    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

// Auth routes (login/forgot/reset/verify/logout)
require __DIR__ . '/auth.php';
