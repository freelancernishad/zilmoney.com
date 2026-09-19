<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Zilmoney\Shop\CategoryController;
use App\Http\Controllers\Zilmoney\Shop\FilterController;
use App\Http\Controllers\Zilmoney\Shop\ProductController;
use App\Http\Controllers\Zilmoney\Shop\OrderController;
use App\Http\Controllers\Zilmoney\Shop\StripeCheckoutController;
use App\Http\Controllers\Zilmoney\Shop\AdminStripeTaxRegistrationController;
use App\Http\Controllers\Zilmoney\Shop\AdminTaxReportController;

// Public & Admin Shop API Endpoints
Route::prefix('v1/shop')->group(function () {
    // Admin Stripe Tax Registration Routes
    Route::get('admin/stripe-tax-registrations', [AdminStripeTaxRegistrationController::class, 'index']);
    Route::post('admin/stripe-tax-registrations', [AdminStripeTaxRegistrationController::class, 'store']);
    Route::post('admin/stripe-tax-registrations/bulk', [AdminStripeTaxRegistrationController::class, 'bulkAdd']);

    // Admin Tax Analytics & Sales Reports Routes
    Route::get('admin/tax-reports', [AdminTaxReportController::class, 'index']);
    Route::get('admin/tax-reports/export-csv', [AdminTaxReportController::class, 'exportCsv']);
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

    // Products CRUD & S3 Image Upload
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{idOrSlug}', [ProductController::class, 'show']);
    Route::post('products', [ProductController::class, 'store']);
    Route::post('upload-image', [ProductController::class, 'uploadImage']);
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

    // Tax Rates API Endpoint
    Route::get('tax-rates', function () {
        $taxRates = [
            ['country' => 'United States', 'state' => 'CA', 'tax_rate' => 7.25],
            ['country' => 'United States', 'state' => 'NY', 'tax_rate' => 8.525],
            ['country' => 'United States', 'state' => 'TX', 'tax_rate' => 6.25],
            ['country' => 'United States', 'state' => 'FL', 'tax_rate' => 6.00],
            ['country' => 'United States', 'state' => 'IL', 'tax_rate' => 6.25],
            ['country' => 'United States', 'state' => 'PA', 'tax_rate' => 6.00],
            ['country' => 'United States', 'state' => 'OH', 'tax_rate' => 5.75],
            ['country' => 'United States', 'state' => 'GA', 'tax_rate' => 4.00],
            ['country' => 'United States', 'state' => 'NC', 'tax_rate' => 4.75],
            ['country' => 'United States', 'state' => 'MI', 'tax_rate' => 6.00],
            ['country' => 'United States', 'state' => 'NJ', 'tax_rate' => 6.625],
            ['country' => 'United States', 'state' => 'VA', 'tax_rate' => 5.30],
            ['country' => 'United States', 'state' => 'WA', 'tax_rate' => 6.50],
            ['country' => 'United States', 'state' => 'MA', 'tax_rate' => 6.25],
            ['country' => 'United States', 'state' => 'AZ', 'tax_rate' => 5.60],
            ['country' => 'United States', 'state' => 'CO', 'tax_rate' => 2.90],
            ['country' => 'United States', 'state' => 'TN', 'tax_rate' => 7.00],
            ['country' => 'United States', 'state' => 'MD', 'tax_rate' => 6.00],
            ['country' => 'United States', 'state' => 'IN', 'tax_rate' => 7.00],
            ['country' => 'United States', 'state' => 'MO', 'tax_rate' => 4.225],
            ['country' => 'United States', 'state' => 'WI', 'tax_rate' => 5.00],
            ['country' => 'United States', 'state' => 'MN', 'tax_rate' => 6.875],
            ['country' => 'United States', 'state' => 'OR', 'tax_rate' => 0.00],
            ['country' => 'United States', 'state' => 'MT', 'tax_rate' => 0.00],
            ['country' => 'United States', 'state' => 'DE', 'tax_rate' => 0.00],
            ['country' => 'United States', 'state' => 'NH', 'tax_rate' => 0.00],
            ['country' => 'United States', 'state' => 'AK', 'tax_rate' => 0.00],
        ];
        return response()->json([
            'isError' => false,
            'data' => $taxRates,
        ]);
    });
});
