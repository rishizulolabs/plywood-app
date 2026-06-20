<x-guest-layout :wide="true">
    <div class="auth-page auth-page-register">
        <div class="auth-page-header">
            <h2 class="auth-page-title">Create an account</h2>
            <p class="auth-page-subtitle">Register to get full access</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="auth-form" x-data="{ accountType: '{{ old('account_type', 'customer') }}' }">
            @csrf

            <div class="auth-form-grid">
                <div class="auth-field">
                    <x-input-label for="name" :value="__('Name')" class="auth-label" />
                    <x-text-input id="name" class="auth-input" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="auth-field-error" />
                </div>

                <div class="auth-field">
                    <x-input-label for="email" :value="__('Email')" class="auth-label" />
                    <x-text-input id="email" class="auth-input" type="email" name="email" :value="old('email')" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="auth-field-error" />
                </div>

                <div class="auth-field auth-form-grid-full">
                    <x-input-label for="account_type" value="I am a..." class="auth-label" />
                    <select id="account_type" name="account_type" class="auth-input auth-select" required x-model="accountType">
                        <option value="customer" @selected(old('account_type', 'customer') === 'customer')>Customer (buy plywood)</option>
                        <option value="distributor" @selected(old('account_type') === 'distributor')>Distributor (sell plywood)</option>
                    </select>
                    <x-input-error :messages="$errors->get('account_type')" class="auth-field-error" />
                </div>

                <div class="auth-field auth-form-grid-full" x-show="accountType === 'distributor'" x-cloak>
                    <x-input-label for="business_name" value="Business name" class="auth-label" />
                    <x-text-input id="business_name" class="auth-input" type="text" name="business_name" :value="old('business_name')" />
                    <x-input-error :messages="$errors->get('business_name')" class="auth-field-error" />
                </div>

                <div class="auth-field">
                    <x-input-label for="password" :value="__('Password')" class="auth-label" />
                    <x-text-input id="password" class="auth-input" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="auth-field-error" />
                </div>

                <div class="auth-field">
                    <x-input-label for="password_confirmation" :value="__('Confirm password')" class="auth-label" />
                    <x-text-input id="password_confirmation" class="auth-input" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="auth-field-error" />
                </div>
            </div>

            <x-primary-button class="auth-btn-primary">
                {{ __('Create account') }}
            </x-primary-button>
        </form>

        <div class="auth-form-footer">
            <p class="auth-form-footer-text">
                Already have an account?
                <a class="auth-link" href="{{ route('login') }}">Sign in</a>
            </p>
        </div>
    </div>
</x-guest-layout>
