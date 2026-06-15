@extends('layouts.panel')

@section('portal-label', 'Admin')
@section('portal-badge', 'Admin')

@if(View::hasSection('page-header-actions'))
    @section('header-actions')
        @yield('page-header-actions')
    @endsection
@endif

@section('sidebar-nav')
    <x-panel.nav-section title="Main" icon="icon-home">
        <x-panel.nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" icon="icon-home">
            Dashboard
        </x-panel.nav-link>
    </x-panel.nav-section>

    <x-panel.nav-section title="Users" icon="icon-users">
        <x-panel.nav-link :href="route('admin.customers.index')" :active="request()->routeIs('admin.customers.*')" icon="icon-user">
            Customers
        </x-panel.nav-link>
        <x-panel.nav-link :href="route('admin.distributors.index')" :active="request()->routeIs('admin.distributors.*')" icon="icon-users">
            Distributors
        </x-panel.nav-link>
    </x-panel.nav-section>

    <x-panel.nav-section title="Catalog" icon="icon-layers">
        <x-panel.nav-link :href="route('admin.products.index')" :active="request()->routeIs('admin.products.*')" icon="icon-database">
            Products
        </x-panel.nav-link>
        <x-panel.nav-link :href="route('admin.categories.index')" :active="request()->routeIs('admin.categories.*')" icon="icon-layers">
            Categories
        </x-panel.nav-link>
    </x-panel.nav-section>

    <x-panel.nav-section title="Sales" icon="icon-file-text">
        <x-panel.nav-link :href="route('admin.customer-orders.index')" :active="request()->routeIs('admin.customer-orders.*')" icon="icon-shopping-cart">
            Customer Orders
        </x-panel.nav-link>
        <x-panel.nav-link :href="route('admin.distributor-orders.index')" :active="request()->routeIs('admin.distributor-orders.*')" icon="icon-package">
            Distributor Orders
        </x-panel.nav-link>
    </x-panel.nav-section>

@endsection
