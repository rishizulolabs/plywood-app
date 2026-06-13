<x-guest-layout>
    <div class="mb-7">
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Welcome back</h2>
        <p class="mt-1.5 text-sm text-slate-500">Sign in to your account to continue</p>
    </div>

    <x-auth-session-status class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="{ showPassword: false }">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email address')" class="text-slate-700" />
            <x-text-input
                id="email"
                class="auth-input mt-1.5"
                type="email"
                name="email"
                :value="old('email')"
                placeholder="you@company.com"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" class="text-slate-700" />
            <div class="relative mt-1.5">
                <input
                    id="password"
                    class="auth-input pr-10"
                    x-bind:type="showPassword ? 'text' : 'password'"
                    name="password"
                    placeholder="Enter your password"
                    required
                    autocomplete="current-password"
                />
                <button
                    type="button"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600 transition"
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
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-4">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input
                    id="remember_me"
                    type="checkbox"
                    class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500"
                    name="remember"
                >
                <span class="ms-2 text-sm text-slate-600">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-blue-600 hover:text-blue-700 transition shrink-0" href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <x-primary-button class="auth-btn-primary !w-full !normal-case !tracking-normal !text-sm !py-2.5">
            {{ __('Sign in') }}
        </x-primary-button>
    </form>

    @if (app()->environment('local'))
        <div class="mt-7 pt-6 border-t border-slate-100" x-data="{
            fillDemo(email) {
                document.getElementById('email').value = email;
                document.getElementById('password').value = 'admin@123';
                document.getElementById('email').focus();
            }
        }">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Quick demo login</p>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="auth-demo-chip" @click="fillDemo('admin@plywood.com')">
                    <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                    Admin
                </button>
                <button type="button" class="auth-demo-chip" @click="fillDemo('customer@plywood.com')">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Customer
                </button>
                <button type="button" class="auth-demo-chip" @click="fillDemo('distributor@plywood.com')">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                    Distributor
                </button>
            </div>
            <p class="mt-2.5 text-xs text-slate-400">Password for all demo accounts: <span class="font-mono text-slate-500">admin@123</span></p>
        </div>
    @endif
</x-guest-layout>
