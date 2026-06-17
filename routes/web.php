<?php

use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DistributorController as AdminDistributorController;
use App\Http\Controllers\Admin\DistributorOrderController as AdminDistributorOrderController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Customer\CatalogController as CustomerCatalogController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\CartController as CustomerCartController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Distributor\DashboardController as DistributorDashboardController;
use App\Http\Controllers\Distributor\OrderController as DistributorOrderController;
use App\Http\Controllers\Distributor\ProductController as DistributorProductController;
use App\Http\Controllers\Distributor\PurchaseOrderController as DistributorPurchaseOrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicCatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers', [AdminCustomerController::class, 'store'])->name('customers.store');
    Route::put('/customers/{customer}', [AdminCustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [AdminCustomerController::class, 'destroy'])->name('customers.destroy');
    Route::get('/distributors', [AdminDistributorController::class, 'index'])->name('distributors.index');
    Route::post('/distributors', [AdminDistributorController::class, 'store'])->name('distributors.store');
    Route::put('/distributors/{distributor}', [AdminDistributorController::class, 'update'])->name('distributors.update');
    Route::patch('/distributors/{distributor}/status', [AdminDistributorController::class, 'updateStatus'])->name('distributors.status');
    Route::delete('/distributors/{distributor}', [AdminDistributorController::class, 'destroy'])->name('distributors.destroy');
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
    Route::get('/customer-orders', [AdminOrderController::class, 'index'])->name('customer-orders.index');
    Route::post('/customer-orders', [AdminOrderController::class, 'store'])->name('customer-orders.store');
    Route::get('/distributor-orders', [AdminDistributorOrderController::class, 'index'])->name('distributor-orders.index');
    Route::patch('/distributor-orders/{restockRequest}/status', [AdminDistributorOrderController::class, 'updateStatus'])->name('distributor-orders.status');
    Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
});

Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/catalog', [CustomerCatalogController::class, 'index'])->name('catalog.index');
    Route::get('/catalog/{product}', [CustomerCatalogController::class, 'show'])->name('catalog.show');
    Route::post('/catalog/{product}/cart', [CustomerCatalogController::class, 'addToCart'])->name('catalog.add-to-cart');
    Route::get('/cart', [CustomerCartController::class, 'index'])->name('cart.index');
    Route::put('/cart/items/{product}', [CustomerCartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/items/{product}', [CustomerCartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/proceed', [CustomerCartController::class, 'proceed'])->name('cart.proceed');
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
});

Route::middleware(['auth', 'role:distributor'])->prefix('distributor')->name('distributor.')->group(function () {
    Route::get('/', [DistributorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/products', [DistributorProductController::class, 'index'])->name('products.index');
    Route::post('/products/{product}/restock', [DistributorProductController::class, 'restock'])->name('products.restock');
    Route::get('/orders', [DistributorOrderController::class, 'index'])->name('orders.index');
    Route::patch('/orders/{order}/status', [DistributorOrderController::class, 'updateStatus'])->name('orders.status');
    Route::get('/purchase-orders', [DistributorPurchaseOrderController::class, 'index'])->name('purchase-orders.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
