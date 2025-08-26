<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// pakai Livewire class untuk login (bukan Volt view)
use App\Livewire\Actions\Login;

Route::middleware('guest')->group(function () {
    // Login page (Livewire class)
    Route::get('login', Login::class)->name('login');

    // Forgot password & reset password tetap Volt
    Volt::route('forgot-password', 'auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'auth.reset-password')
        ->name('password.reset');
});

Route::middleware('auth')->group(function () {
    // Email verification (jika dipakai)
    Volt::route('verify-email', 'auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'auth.confirm-password')
        ->name('password.confirm');
});

// Logout (Livewire action)
Route::post('logout', App\Livewire\Actions\Logout::class)
    ->name('logout');
