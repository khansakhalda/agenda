<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;

class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke()
    {
        Auth::guard('web')->logout();

        // Bereskan sesi
        session()->invalidate();
        session()->regenerateToken();

        // Kirim flash toast lalu arahkan ke halaman login
        return redirect()
            ->route('login')
            ->with('toast', [
                'type'  => 'success',
                'title' => 'Signed out',
                'text'  => 'You have been logged out safely.',
            ]);
    }
}
