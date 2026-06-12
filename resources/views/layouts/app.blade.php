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
</head>
<body class="bg-white text-slate-800 antialiased">
    <nav class="border-b border-slate-200 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="font-semibold text-amber-700">{{ config('app.name') }}</a>
            <div class="flex items-center gap-6 text-sm">
                <a href="{{ route('home') }}" class="text-slate-600 hover:text-slate-900">Browse</a>
                @auth
                    @role('customer')
                        <a href="{{ route('customer.dashboard') }}" class="text-slate-600 hover:text-slate-900">My Dashboard</a>
                    @endrole
                    @role('distributor')
                        <a href="{{ route('distributor.dashboard') }}" class="text-slate-600 hover:text-slate-900">Distributor Panel</a>
                    @endrole
                    @role('admin')
                        <a href="/admin" class="text-slate-600 hover:text-slate-900">Admin Panel</a>
                    @endrole
                    <form method="POST" action="{{ route('logout') }}" class="inline">@csrf<button type="submit" class="text-slate-600 hover:text-slate-900">Logout</button></form>
                @else
                    <a href="{{ route('login') }}" class="text-slate-600 hover:text-slate-900">Login</a>
                    <a href="{{ route('register') }}" class="px-3 py-1.5 rounded-md bg-amber-600 text-white hover:bg-amber-700">Register</a>
                @endauth
            </div>
        </div>
    </nav>
    <main>
        @yield('content')
    </main>
    @livewireScripts
</body>
</html>
