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
            $this->dispatch('toast', type: 'error', title: 'Login failed', text: 'Incorrect email or password.');
            return;
        }

        // sukses
        RateLimiter::clear($this->throttleKey());
        session()->regenerate();

        // flash untuk halaman tujuan
        session()->flash('toast', [
            'type'  => 'success',
            'title' => 'Signed in',
            'text'  => 'Welcome back!',
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

        $this->dispatch('toast', type: 'error', title: 'Too many attempts', text: "Try again in {$seconds} seconds.");

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
        return view('livewire.auth.login');
    }
}
