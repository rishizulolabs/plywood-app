<x-guest-layout>
    <div class="auth-page">
        <div class="auth-page-header">
            <h2 class="auth-page-title">Welcome back</h2>
            <p class="auth-page-subtitle">Sign in to your account to continue</p>
        </div>

        <x-auth-session-status class="auth-session-status" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="auth-form" x-data="{ showPassword: false }">
            @csrf

            <div class="auth-field">
                <x-input-label for="email" :value="__('Email address')" class="auth-label" />
                <x-text-input
                    id="email"
                    class="auth-input"
                    type="email"
                    name="email"
                    :value="old('email')"
                    placeholder="you@company.com"
                    required
                    autofocus
                    autocomplete="username"
                />
                <x-input-error :messages="$errors->get('email')" class="auth-field-error" />
            </div>

            <div class="auth-field">
                <x-input-label for="password" :value="__('Password')" class="auth-label" />
                <div class="auth-password-wrap">
                    <input
                        id="password"
                        class="auth-input auth-input-password"
                        x-bind:type="showPassword ? 'text' : 'password'"
                        name="password"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    />
                    <button
                        type="button"
                        class="auth-password-toggle"
                        @click="showPassword = !showPassword"
                        tabindex="-1"
                        aria-label="Toggle password visibility"
                    >
                        <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="auth-field-error" />
            </div>

            <div class="auth-form-row">
                <label for="remember_me" class="auth-checkbox-label">
                    <input
                        id="remember_me"
                        type="checkbox"
                        class="auth-checkbox"
                        name="remember"
                    >
                    <span>{{ __('Remember me') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="auth-link auth-link-sm shrink-0" href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <x-primary-button class="auth-btn-primary">
                {{ __('Sign in') }}
            </x-primary-button>
        </form>

        <div class="auth-form-footer">
            <p class="auth-form-footer-text">
                Don't have an account?
                <a class="auth-link" href="{{ route('register') }}">Sign up</a>
            </p>
        </div>

        @if (app()->environment('local'))
            <div
                class="auth-demo-section"
                x-data="{
                    fillDemo(email) {
                        document.getElementById('email').value = email;
                        document.getElementById('password').value = 'admin@123';
                        document.getElementById('email').focus();
                    }
                }"
            >
                <p class="auth-demo-title">Quick demo login</p>
                <div class="auth-demo-chips">
                    <button type="button" class="auth-demo-chip" @click="fillDemo('admin@plywood.com')">
                        <span class="auth-demo-dot auth-demo-dot-blue"></span>
                        Admin
                    </button>
                    <button type="button" class="auth-demo-chip" @click="fillDemo('customer@plywood.com')">
                        <span class="auth-demo-dot auth-demo-dot-green"></span>
                        Customer
                    </button>
                    <button type="button" class="auth-demo-chip" @click="fillDemo('distributor@plywood.com')">
                        <span class="auth-demo-dot auth-demo-dot-amber"></span>
                        Distributor
                    </button>
                </div>
                <p class="auth-demo-hint">
                    Password for all demo accounts:
                    <code class="auth-demo-code">admin@123</code>
                </p>
            </div>
        @endif
    </div>
</x-guest-layout>
