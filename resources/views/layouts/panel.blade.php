<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    <link
        rel="stylesheet"
        href="{{ asset('css/admin-theme/admin-dashboard-theme.css') }}"
        id="admin-theme-css"
        data-light="{{ asset('css/admin-theme/admin-dashboard-theme.css') }}"
        data-dark="{{ asset('css/admin-theme/admin-dashboard-theme-dark.css') }}"
    >
    @livewireStyles
    @stack('styles')
</head>
<body>
    @include('partials.admin-theme-icons')

    <div class="dashboard-wrap">
        <div class="sidebar-overlay" id="sidebar-overlay" aria-hidden="true"></div>

        <div class="main-with-sidebar" id="main-with-sidebar">
            <aside class="sidebar" id="sidebar">
                <div class="sidebar-brand">
                    <div class="sidebar-brand-inner">
                        <div class="sidebar-logo">{{ strtoupper(substr(config('app.name'), 0, 2)) }}</div>
                        <div class="sidebar-brand-text">
                            <p>{{ config('app.name') }}</p>
                            <p>@yield('portal-label', 'Portal')</p>
                        </div>
                    </div>
                </div>

                <nav class="sidebar-nav">
                    @yield('sidebar-nav')
                </nav>

                <div class="sidebar-footer">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-logout">
                            <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-log-out"></use></svg>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </aside>

            <main class="main-content">
                <div class="page-header">
                    <button
                        type="button"
                        class="sidebar-toggle sidebar-toggle-inline"
                        id="sidebar-toggle-inline"
                        aria-label="Toggle sidebar"
                        aria-expanded="true"
                    >
                        <svg class="icon-svg" aria-hidden="true"><use href="#icon-menu"></use></svg>
                    </button>

                    <div class="page-header-title">
                        @hasSection('page-heading')
                            @yield('page-heading')
                        @else
                            <h1>@yield('page-title', 'Dashboard')</h1>
                            @hasSection('page-subtitle')
                                <p>@yield('page-subtitle')</p>
                            @endif
                        @endif
                    </div>

                    @if(View::hasSection('header-actions'))
                        <div class="header-actions">
                            @yield('header-actions')
                        </div>
                    @endif
                </div>

                @if(session('success') || session('error') || $errors->any())
                    <div class="alerts-stack">
                        @if(session('success'))
                            <div class="alert alert-success" role="alert">
                                <svg class="alert-icon" aria-hidden="true"><use href="#icon-check-circle"></use></svg>
                                <p class="alert-message">{{ session('success') }}</p>
                                <button type="button" class="alert-dismiss" aria-label="Dismiss">
                                    <svg class="icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
                                </button>
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-error" role="alert">
                                <svg class="alert-icon" aria-hidden="true"><use href="#icon-alert-circle"></use></svg>
                                <p class="alert-message">{{ session('error') }}</p>
                                <button type="button" class="alert-dismiss" aria-label="Dismiss">
                                    <svg class="icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
                                </button>
                            </div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-error" role="alert">
                                <svg class="alert-icon" aria-hidden="true"><use href="#icon-alert-circle"></use></svg>
                                <p class="alert-message">{{ $errors->first() }}</p>
                                <button type="button" class="alert-dismiss" aria-label="Dismiss">
                                    <svg class="icon-svg" aria-hidden="true"><use href="#icon-x"></use></svg>
                                </button>
                            </div>
                        @endif
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="{{ asset('js/admin-theme.js') }}"></script>
    @livewireScripts
    @stack('scripts')
</body>
</html>
