@extends('layouts.app')

@section('title', 'Browse Plywood Products')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold text-slate-900">B2B Plywood Marketplace</h1>
        <p class="mt-4 text-slate-600">Browse products and place orders directly with verified distributors.</p>
        @guest
            <p class="mt-6 text-sm text-amber-700">Sign in to add products to your cart.</p>
        @endguest
    </div>
    <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-6 rounded-lg border border-slate-200 bg-slate-50">
            <h3 class="font-semibold">1. Browse catalog</h3>
            <p class="mt-2 text-sm text-slate-600">Explore plywood by category, thickness, and brand.</p>
        </div>
        <div class="p-6 rounded-lg border border-slate-200 bg-slate-50">
            <h3 class="font-semibold">2. Add to cart</h3>
            <p class="mt-2 text-sm text-slate-600">Select products and quantities for your project.</p>
        </div>
        <div class="p-6 rounded-lg border border-slate-200 bg-slate-50">
            <h3 class="font-semibold">3. Place your order</h3>
            <p class="mt-2 text-sm text-slate-600">Proceed from your cart to send the order to a distributor.</p>
        </div>
    </div>
</div>
@endsection
