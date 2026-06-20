<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>HATTRICK — {{ $title ?? 'Sign in' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endif
    <link rel="stylesheet" href="{{ asset('css/auth-theme.css') }}?v={{ filemtime(public_path('css/auth-theme.css')) }}">
</head>
<body class="font-sans text-slate-900 antialiased">
    <div class="auth-shell min-h-screen flex">
        <div class="auth-brand-panel hidden lg:flex lg:w-[45%] xl:w-1/2 flex-col justify-between p-10 xl:p-14 text-white">
            <div class="auth-brand-panel-top">
                <x-auth.hattrick-brand />
            </div>

            <div class="auth-brand-copy max-w-md">
                <h1 class="auth-brand-heading">
                    B2B plywood ordering, simplified
                </h1>
                <p class="auth-brand-lead">
                    Browse products, add to cart, and place orders with verified distributors.
                </p>
                <ul class="auth-brand-steps">
                    <li>
                        <span class="auth-brand-step-num">1</span>
                        Explore catalog by category, thickness &amp; grade
                    </li>
                    <li>
                        <span class="auth-brand-step-num">2</span>
                        Add products to your cart
                    </li>
                    <li>
                        <span class="auth-brand-step-num">3</span>
                        Place orders directly with distributors
                    </li>
                </ul>
            </div>

            <p class="auth-brand-footer">
                &copy; {{ date('Y') }} HATTRICK. All rights reserved.
            </p>
        </div>

        <div class="auth-form-panel flex flex-1 flex-col items-center justify-center px-6 py-6 sm:px-10 sm:py-8">
            <div class="auth-mobile-logo lg:hidden text-center">
                <x-auth.hattrick-brand />
            </div>

            <div @class([
                'auth-form-shell w-full mx-auto',
                'max-w-[32rem]' => $wide,
                'max-w-[26rem]' => ! $wide,
            ])>
                <div @class(['auth-form-card', 'auth-form-card-wide' => $wide])>
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
