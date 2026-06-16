<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    @livewireStyles
    <style>
        #site-header[data-header-blend] {
            background-color: rgb(255 251 235 / 0.8);
            border-bottom-color: transparent;
        }

        #site-header[data-header-blend].is-scrolled {
            background-color: rgb(255 255 255 / 0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom-color: rgb(226 232 240 / 0.8);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">
    <nav
        id="site-header"
        class="sticky top-0 z-30 border-b transition-[background-color,border-color,backdrop-filter] duration-300 {{ request()->routeIs('home') ? '' : 'border-slate-200/80 bg-white/90 backdrop-blur' }}"
        @if(request()->routeIs('home')) data-header-blend @endif
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold text-amber-700">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-amber-600 text-xs font-bold text-white">PM</span>
                <span>{{ config('app.name') }}</span>
            </a>
            <div class="flex items-center gap-4 sm:gap-6 text-sm">
                @auth
                    @role('customer')
                        <a href="{{ route('customer.catalog.index') }}" class="text-slate-600 hover:text-slate-900 font-medium">Catalog</a>
                        <a href="{{ route('customer.dashboard') }}" class="text-slate-600 hover:text-slate-900 font-medium">Dashboard</a>
                    @endrole
                @else
                    <a href="{{ route('home') }}" class="text-slate-600 hover:text-slate-900 font-medium">Home</a>
                @endauth
                @auth
                    @role('distributor')
                        <a href="{{ route('distributor.dashboard') }}" class="text-slate-600 hover:text-slate-900 font-medium">Distributor Panel</a>
                    @endrole
                    @role('admin')
                        <a href="/admin" class="text-slate-600 hover:text-slate-900 font-medium">Admin Panel</a>
                    @endrole
                    <form method="POST" action="{{ route('logout') }}" class="inline">@csrf<button type="submit" class="text-slate-600 hover:text-slate-900 font-medium">Logout</button></form>
                @else
                    <a href="{{ route('login') }}" class="text-slate-600 hover:text-slate-900 font-medium">Login</a>
                    <a href="{{ route('register') }}" class="px-3.5 py-2 rounded-lg bg-amber-600 text-white font-semibold hover:bg-amber-700 shadow-sm transition">Register</a>
                @endauth
            </div>
        </div>
    </nav>
    <main class="flex-1">
        @yield('content')
    </main>
    @include('partials.site-footer')
    @livewireScripts
    @if(request()->routeIs('home'))
        <script>
            (function () {
                var header = document.getElementById('site-header');
                if (!header || !header.hasAttribute('data-header-blend')) {
                    return;
                }

                function updateHeader() {
                    header.classList.toggle('is-scrolled', window.scrollY > 12);
                }

                updateHeader();
                window.addEventListener('scroll', updateHeader, { passive: true });
            })();
        </script>
    @endif
</body>
</html>
