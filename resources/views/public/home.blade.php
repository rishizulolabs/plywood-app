@extends('layouts.app')

@section('title', 'Browse Plywood Products')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold text-slate-900">B2B Plywood Marketplace</h1>
        <p class="mt-4 text-slate-600">Browse products and request quotes. No public pricing — negotiate directly with distributors.</p>
        @guest
            <p class="mt-6 text-sm text-amber-700">Sign in to add products to your inquiry cart.</p>
        @endguest
    </div>
    <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-6 rounded-lg border border-slate-200 bg-slate-50">
            <h3 class="font-semibold">1. Browse catalog</h3>
            <p class="mt-2 text-sm text-slate-600">Explore plywood by category, thickness, and brand.</p>
        </div>
        <div class="p-6 rounded-lg border border-slate-200 bg-slate-50">
            <h3 class="font-semibold">2. Submit inquiry</h3>
            <p class="mt-2 text-sm text-slate-600">Add items to cart and request a quote from distributors.</p>
        </div>
        <div class="p-6 rounded-lg border border-slate-200 bg-slate-50">
            <h3 class="font-semibold">3. Receive custom quote</h3>
            <p class="mt-2 text-sm text-slate-600">Pricing is shared only after a distributor responds.</p>
        </div>
    </div>
</div>
@endsection
