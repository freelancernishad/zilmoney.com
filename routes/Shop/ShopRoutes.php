<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Zilmoney\Shop\CategoryController;
use App\Http\Controllers\Zilmoney\Shop\FilterController;
use App\Http\Controllers\Zilmoney\Shop\ProductController;
use App\Http\Controllers\Zilmoney\Shop\OrderController;
use App\Http\Controllers\Zilmoney\Shop\StripeCheckoutController;

// Public & Admin Shop API Endpoints
Route::prefix('v1/shop')->group(function () {
    // Categories CRUD
    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);
    Route::put('categories/{id}', [CategoryController::class, 'update']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);

    // Filters & Filter Groups CRUD
    Route::get('filters', [FilterController::class, 'index']);
    Route::post('filters', [FilterController::class, 'store']);
    Route::post('filter-groups', [FilterController::class, 'storeGroup']);
    Route::delete('filter-groups/{id}', [FilterController::class, 'destroyGroup']);
    Route::delete('filter-values/{id}', [FilterController::class, 'destroyValue']);

    // Products CRUD
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{idOrSlug}', [ProductController::class, 'show']);
    Route::post('products', [ProductController::class, 'store']);
    Route::put('products/{id}', [ProductController::class, 'update']);
    Route::delete('products/{id}', [ProductController::class, 'destroy']);

    // Orders CRUD & Status
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{id}', [OrderController::class, 'show']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::put('orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::delete('orders/{id}', [OrderController::class, 'destroy']);

    // Stripe Session Payment & Guest Checkout
    Route::post('create-checkout-session', [StripeCheckoutController::class, 'createSession']);
    Route::get('verify-checkout-session', [StripeCheckoutController::class, 'verifySession']);
});
