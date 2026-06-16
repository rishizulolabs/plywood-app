@extends('layouts.app')

@section('title', 'B2B Plywood Marketplace')

@section('content')
<div class="relative overflow-hidden bg-gradient-to-b from-amber-50/80 via-white to-slate-50">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-72 bg-[radial-gradient(ellipse_at_top,_rgba(217,119,6,0.12),_transparent_60%)]"></div>

    <section class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-20">
        <div class="max-w-3xl mx-auto text-center">
            <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-800">
                B2B plywood sourcing
            </span>
            <h1 class="mt-5 text-4xl sm:text-5xl font-bold tracking-tight text-slate-900">
                Order plywood directly from verified distributors
            </h1>
            <p class="mt-5 text-lg text-slate-600 leading-relaxed">
                Browse catalog products, build your cart, and place orders with approved distributors — all in one place.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                @auth
                    @role('customer')
                        <a href="{{ route('customer.catalog.index') }}" class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-amber-700 transition">
                            Browse catalog
                        </a>
                        <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                            My dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-amber-700 transition">
                            Go to dashboard
                        </a>
                    @endrole
                @else
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-amber-700 transition">
                        Create account
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                        Sign in
                    </a>
                @endauth
            </div>

            @guest
                <p class="mt-4 text-sm text-amber-800/80">Sign in to add products to your cart and place orders.</p>
            @endguest
        </div>

        <dl class="mt-14 grid grid-cols-1 sm:grid-cols-3 gap-4 max-w-4xl mx-auto">
            <div class="rounded-xl border border-slate-200/80 bg-white/80 backdrop-blur px-5 py-4 text-center shadow-sm">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Products listed</dt>
                <dd class="mt-1 text-2xl font-bold text-slate-900">{{ $productCount }}</dd>
            </div>
            <div class="rounded-xl border border-slate-200/80 bg-white/80 backdrop-blur px-5 py-4 text-center shadow-sm">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Approved distributors</dt>
                <dd class="mt-1 text-2xl font-bold text-slate-900">{{ $distributorCount }}</dd>
            </div>
            <div class="rounded-xl border border-slate-200/80 bg-white/80 backdrop-blur px-5 py-4 text-center shadow-sm">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Categories</dt>
                <dd class="mt-1 text-2xl font-bold text-slate-900">{{ $categoryCount }}</dd>
            </div>
        </dl>
    </section>
</div>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
    <div class="text-center max-w-2xl mx-auto">
        <h2 class="text-2xl font-bold text-slate-900">How it works</h2>
        <p class="mt-2 text-slate-600">Three simple steps from browsing to fulfillment.</p>
    </div>

    <div class="mt-10 grid grid-cols-1 md:grid-cols-3 gap-6">
        <article class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md hover:border-amber-200 transition">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-700 font-bold text-sm">1</div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">Browse catalog</h3>
            <p class="mt-2 text-sm text-slate-600 leading-relaxed">Explore plywood by category, thickness, grade, and brand from approved distributors.</p>
        </article>
        <article class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md hover:border-amber-200 transition">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-700 font-bold text-sm">2</div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">Add to cart</h3>
            <p class="mt-2 text-sm text-slate-600 leading-relaxed">Select products and quantities for your project. Your cart is saved to your account.</p>
        </article>
        <article class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md hover:border-amber-200 transition">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-700 font-bold text-sm">3</div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">Place your order</h3>
            <p class="mt-2 text-sm text-slate-600 leading-relaxed">Proceed from your cart to send the order to your assigned distributor for fulfillment.</p>
        </article>
    </div>

    <div class="mt-12 rounded-2xl border border-slate-200 bg-slate-900 px-6 py-8 sm:px-10 sm:py-10 text-center">
        <h2 class="text-xl sm:text-2xl font-bold text-white">Ready to source plywood for your next project?</h2>
        <p class="mt-2 text-slate-300 max-w-xl mx-auto">Join as a customer to browse products, manage your cart, and track orders with your distributor.</p>
        @guest
            <a href="{{ route('register') }}" class="mt-6 inline-flex items-center justify-center rounded-lg bg-amber-500 px-5 py-3 text-sm font-semibold text-white hover:bg-amber-400 transition">
                Get started — it&apos;s free
            </a>
        @else
            @role('customer')
                <a href="{{ route('customer.catalog.index') }}" class="mt-6 inline-flex items-center justify-center rounded-lg bg-amber-500 px-5 py-3 text-sm font-semibold text-white hover:bg-amber-400 transition">
                    Browse catalog now
                </a>
            @endrole
        @endguest
    </div>
</section>
@endsection
