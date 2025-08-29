<?php

namespace App\Livewire\Actions;

use Livewire\Component;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool  $remember = false;

    protected array $rules = [
        'email'    => 'required|string|email',
        'password' => 'required|string',
    ];

    public function login(): void
    {
        $this->validate();
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(
            ['email' => $this->email, 'password' => $this->password],
            $this->remember
        )) {
            RateLimiter::hit($this->throttleKey());

            // error pada form + popup gagal
            $this->addError('email', __('auth.failed'));
            $this->dispatch('toast', type: 'error', title: 'Gagal Masuk', text: 'Email atau kata sandi salah.');
            return;
        }

        // sukses
        RateLimiter::clear($this->throttleKey());
        session()->regenerate();

        // flash untuk halaman tujuan
        session()->flash('toast', [
            'type'  => 'success',
            'title' => 'Berhasil Masuk',
            'text'  => 'Selamat datang kembali!',
        ]);

        // full reload supaya flash pasti terbaca
        $this->redirectIntended(
            default: route('calendar.admin', absolute: false),
            navigate: false   
        );
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) return;

        event(new Lockout(request()));
        $seconds = RateLimiter::availableIn($this->throttleKey());

        $this->dispatch('toast', type: 'error', title: 'Terlalu Banyak Percobaan', text: "Coba lagi dalam {$seconds} detik.");

        $this->addError('email', __('auth.throttle', [
            'seconds' => $seconds,
            'minutes' => ceil($seconds / 60),
        ]));
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }

    public function render()
    {
        return view('livewire.auth.login')->title('Masuk | Agenda App');
    }
}
