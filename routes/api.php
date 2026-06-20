<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Customer\CartController as ApiCustomerCartController;
use App\Http\Controllers\Api\Customer\CatalogController as ApiCustomerCatalogController;
use App\Http\Controllers\Api\Customer\OrderController as ApiCustomerOrderController;
use App\Http\Controllers\Api\Customer\WarrantyClaimController as ApiCustomerWarrantyClaimController;
use App\Http\Controllers\Api\Distributor\DashboardController as ApiDistributorDashboardController;
use App\Http\Controllers\Api\Distributor\OrderController as ApiDistributorOrderController;
use App\Http\Controllers\Api\Distributor\ProductController as ApiDistributorProductController;
use App\Http\Controllers\Api\Distributor\PurchaseOrderController as ApiDistributorPurchaseOrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/', function () {
        return response()->json([
            'app' => 'Hattrick Ply API',
            'version' => 'v1',
            'status' => 'ok',
            'endpoints' => [
                'POST /api/v1/login',
                'POST /api/v1/register',
                'GET /api/v1/me (auth)',
                'GET /api/v1/customer/catalog (customer)',
                'GET /api/v1/customer/warranty-claims (customer)',
                'POST /api/v1/customer/warranty-claims (customer)',
                'GET /api/v1/distributor/dashboard (distributor)',
            ],
        ]);
    });

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/auth/firebase', [AuthController::class, 'firebaseLogin']);
    Route::post('/auth/phone/check', [AuthController::class, 'checkPhoneLogin']);
    Route::post('/auth/phone/send', [AuthController::class, 'sendPhoneOtp'])->middleware('throttle:6,1');
    Route::post('/auth/phone/verify', [AuthController::class, 'verifyPhoneOtp'])->middleware('throttle:12,1');
    Route::post('/register', [AuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::patch('/profile', [AuthController::class, 'updateProfile']);

        Route::middleware('role:customer')->prefix('customer')->group(function () {
            Route::get('/catalog', [ApiCustomerCatalogController::class, 'index']);
            Route::get('/catalog/{product}', [ApiCustomerCatalogController::class, 'show']);
            Route::get('/categories', [ApiCustomerCatalogController::class, 'categories']);
            Route::get('/cart', [ApiCustomerCartController::class, 'index']);
            Route::post('/cart/place-order', [ApiCustomerCartController::class, 'placeOrder']);
            Route::post('/cart/{product}', [ApiCustomerCartController::class, 'add'])->whereNumber('product');
            Route::put('/cart/{product}', [ApiCustomerCartController::class, 'update'])->whereNumber('product');
            Route::delete('/cart/{product}', [ApiCustomerCartController::class, 'remove'])->whereNumber('product');
            Route::get('/orders', [ApiCustomerOrderController::class, 'index']);
            Route::get('/orders/{order}', [ApiCustomerOrderController::class, 'show']);
            Route::get('/warranty-claims', [ApiCustomerWarrantyClaimController::class, 'index']);
            Route::post('/warranty-claims', [ApiCustomerWarrantyClaimController::class, 'store']);
        });

        Route::middleware('role:distributor')->prefix('distributor')->group(function () {
            Route::get('/dashboard', [ApiDistributorDashboardController::class, 'index']);
            Route::get('/products', [ApiDistributorProductController::class, 'index']);
            Route::post('/products/{product}/restock', [ApiDistributorProductController::class, 'restock']);
            Route::get('/orders', [ApiDistributorOrderController::class, 'index']);
            Route::get('/orders/{order}', [ApiDistributorOrderController::class, 'show']);
            Route::patch('/orders/{order}/status', [ApiDistributorOrderController::class, 'updateStatus']);
            Route::get('/purchase-orders', [ApiDistributorPurchaseOrderController::class, 'index']);
        });
    });
});
