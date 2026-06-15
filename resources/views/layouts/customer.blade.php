@extends('layouts.panel')

@section('portal-label', 'Customer Portal')
@section('portal-badge', 'Customer')

@section('sidebar-nav')
    <x-panel.nav-section title="Main" icon="icon-home">
        <x-panel.nav-link :href="route('customer.dashboard')" :active="request()->routeIs('customer.dashboard')" icon="icon-home">
            Dashboard
        </x-panel.nav-link>
        <x-panel.nav-link :href="route('customer.catalog.index')" :active="request()->routeIs('customer.catalog.*')" icon="icon-layers">
            Browse Catalog
        </x-panel.nav-link>
    </x-panel.nav-section>

    <x-panel.nav-section title="Orders" icon="icon-package">
        <x-panel.nav-link :href="route('customer.cart.index')" :active="request()->routeIs('customer.cart.*')" icon="icon-shopping-cart">
            Cart
        </x-panel.nav-link>
        <x-panel.nav-link :href="route('customer.orders.index')" :active="request()->routeIs('customer.orders.*')" icon="icon-package">
            Orders
        </x-panel.nav-link>
    </x-panel.nav-section>

    <x-panel.nav-section title="Account" icon="icon-user">
        <x-panel.nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')" icon="icon-user">
            Profile
        </x-panel.nav-link>
    </x-panel.nav-section>
@endsection
