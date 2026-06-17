<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Plywood — {{ $title ?? 'Sign in' }}</title>
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
        {{-- Brand panel --}}
        <div class="auth-brand-panel hidden lg:flex lg:w-[45%] xl:w-1/2 flex-col justify-between p-10 xl:p-14 text-white">
            <div>
                <x-plywood-logo variant="inverse" :show-icon="false" class="group hover:opacity-90 transition" />
            </div>

            <div class="max-w-md">
                <h1 class="text-3xl xl:text-4xl font-bold leading-tight tracking-tight">
                    B2B plywood ordering, simplified
                </h1>
                <p class="mt-4 text-blue-100 text-base leading-relaxed">
                    Browse products, add to cart, and place orders with verified distributors.
                </p>
                <ul class="mt-8 space-y-4">
                    <li class="flex items-start gap-3 text-sm text-blue-50">
                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/20 text-xs">1</span>
                        Explore catalog by category, thickness &amp; grade
                    </li>
                    <li class="flex items-start gap-3 text-sm text-blue-50">
                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/20 text-xs">2</span>
                        Add products to your cart
                    </li>
                    <li class="flex items-start gap-3 text-sm text-blue-50">
                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/20 text-xs">3</span>
                        Place orders directly with distributors
                    </li>
                </ul>
            </div>

            <p class="text-sm text-blue-200/80">
                &copy; {{ date('Y') }} Plywood. All rights reserved.
            </p>
        </div>

        {{-- Form panel --}}
        <div class="auth-form-panel flex flex-1 flex-col items-center justify-center px-6 py-6 sm:px-10 sm:py-8">
            <div class="auth-mobile-logo lg:hidden text-center">
                <x-plywood-logo :show-icon="false" />
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
