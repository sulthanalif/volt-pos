<?php

use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title};

new #[Layout('components.layouts.guest')] #[Title('Login')] class extends Component {
    use Toast;

    public string $email;
    public string $password;
    public bool $remember = false;

    public function login(): void
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            Log::channel('auth')->warning('User failed login', [
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
                'time' => now()->toDateTimeString(),
                'email' => $this->email,
            ]);

            $this->warning('Email atau password salah.', position: 'toast-bottom');
            return;
        }

        RateLimiter::clear($this->throttleKey());

        Session::regenerate();

        Log::channel('auth')->info('User logged in', [
            'user' => auth()->user(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'time' => now()->toDateTimeString(),
        ]);

        $this->redirectIntended(default: route ('dashboard'), navigate: true);
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}; ?>

<div class="flex items-center justify-center min-h-screen h-screen">
    <div class="w-full max-w-md">
        <div class="rounded-lg shadow p-6 bg-base-100">
            <div class="flex w-full justify-center">
                <x-app-brand class="mb-4"  />
            </div>
            <!-- <div class="font-bold text-2xl text-center">
                <p>Login</p>
            </div> -->
            <div class="mt-6">
                <x-form wire:submit="login">
                    <x-input label="Email" icon-right="o-user" type="email" wire:model="email"   autofocus />
                    <x-password label="Password" type="password" wire:model="password"  right />
                    <div class="flex justify-between items-center my-3">
                        <x-toggle label="Ingat saya" wire:model="remember" />
                        {{-- <a class=" cursor-pointer hover:underline" href="{{ route('forgot-password') }}"
                            wire:navigate>Lupa Password?</a> --}}
                    </div>
                    <x-slot:actions>
                        <x-button label="Login" class="btn-primary" type="submit" spinner="login" />
                    </x-slot:actions>
                </x-form>
            </div>
        </div>
    </div>
</div>
