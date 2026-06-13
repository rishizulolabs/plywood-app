<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} — Sign in</title>
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
    <div class="min-h-screen flex">
        {{-- Brand panel --}}
        <div class="auth-brand-panel hidden lg:flex lg:w-[45%] xl:w-1/2 flex-col justify-between p-10 xl:p-14 text-white">
            <div>
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3 group">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-white/15 text-sm font-bold ring-1 ring-white/25 group-hover:bg-white/25 transition">
                        PM
                    </span>
                    <span class="text-lg font-semibold tracking-tight">{{ config('app.name') }}</span>
                </a>
            </div>

            <div class="max-w-md">
                <h1 class="text-3xl xl:text-4xl font-bold leading-tight tracking-tight">
                    B2B plywood quotes, simplified
                </h1>
                <p class="mt-4 text-blue-100 text-base leading-relaxed">
                    Browse products, submit inquiries, and receive custom quotes from verified distributors — no public pricing.
                </p>
                <ul class="mt-8 space-y-4">
                    <li class="flex items-start gap-3 text-sm text-blue-50">
                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/20 text-xs">1</span>
                        Explore catalog by category, thickness &amp; grade
                    </li>
                    <li class="flex items-start gap-3 text-sm text-blue-50">
                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/20 text-xs">2</span>
                        Request quotes tailored to your project
                    </li>
                    <li class="flex items-start gap-3 text-sm text-blue-50">
                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/20 text-xs">3</span>
                        Confirm orders after distributor negotiation
                    </li>
                </ul>
            </div>

            <p class="text-sm text-blue-200/80">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>

        {{-- Form panel --}}
        <div class="flex flex-1 flex-col items-center justify-center bg-slate-50 px-6 py-10 sm:px-10">
            <div class="mb-6 lg:hidden text-center">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white">PM</span>
                    <span class="text-lg font-semibold text-slate-900">{{ config('app.name') }}</span>
                </a>
            </div>

            <div class="w-full max-w-md">
                <div class="rounded-2xl bg-white px-8 py-9 shadow-xl shadow-slate-200/60 ring-1 ring-slate-100">
                    {{ $slot }}
                </div>

                <p class="mt-6 text-center text-sm text-slate-500">
                    <a href="{{ url('/') }}" class="text-blue-600 hover:text-blue-700 font-medium transition">&larr; Back to marketplace</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
