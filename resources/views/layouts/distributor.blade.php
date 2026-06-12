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
        <x-panel.nav-link href="#" icon="icon-database">
            Products
        </x-panel.nav-link>
    </x-panel.nav-section>

    <x-panel.nav-section title="Sales" icon="icon-file-text">
        <x-panel.nav-link href="#" icon="icon-file-text">
            Inquiries
        </x-panel.nav-link>
        <x-panel.nav-link href="#" icon="icon-package">
            Orders
        </x-panel.nav-link>
    </x-panel.nav-section>

    <x-panel.nav-section title="Account" icon="icon-user">
        <x-panel.nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')" icon="icon-user">
            Business Profile
        </x-panel.nav-link>
    </x-panel.nav-section>
@endsection
