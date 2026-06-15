@extends('layouts.panel')

@section('portal-label', 'Distributor Portal')
@section('portal-badge', 'Distributor')

@section('sidebar-nav')
    <x-panel.nav-section title="Main" icon="icon-home">
        <x-panel.nav-link :href="route('distributor.dashboard')" :active="request()->routeIs('distributor.dashboard')" icon="icon-home">
            Overview
        </x-panel.nav-link>
    </x-panel.nav-section>

    <x-panel.nav-section title="Catalog" icon="icon-layers">
        <x-panel.nav-link :href="route('distributor.products.index')" :active="request()->routeIs('distributor.products.*')" icon="icon-database">
            Products
        </x-panel.nav-link>
    </x-panel.nav-section>

    <x-panel.nav-section title="Sales" icon="icon-package">
        <x-panel.nav-link :href="route('distributor.orders.index')" :active="request()->routeIs('distributor.orders.*')" icon="icon-shopping-cart">
            Customer Orders
        </x-panel.nav-link>
        <x-panel.nav-link :href="route('distributor.purchase-orders.index')" :active="request()->routeIs('distributor.purchase-orders.*')" icon="icon-file-text">
            Purchase Orders
        </x-panel.nav-link>
    </x-panel.nav-section>
@endsection
