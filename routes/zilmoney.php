<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Zilmoney\DashboardController;
use App\Http\Controllers\Zilmoney\BusinessController;
use App\Http\Controllers\Zilmoney\AccountController;
use App\Http\Controllers\Zilmoney\PayeeController;
use App\Http\Controllers\Zilmoney\PaymentController;
use App\Http\Controllers\Zilmoney\PlaidController;

// Dashboard
Route::get('dashboard', [DashboardController::class, 'index']);

// Business Profile
Route::apiResource('business', BusinessController::class);

// Banking
Route::post('accounts/validate-routing', [AccountController::class, 'validateRouting']);
Route::apiResource('accounts', AccountController::class);

// Payees
Route::apiResource('payees', PayeeController::class);

// Payments
Route::apiResource('payments', PaymentController::class);
Route::get('payments/{id}/pdf', [PaymentController::class, 'downloadPdf']);

// Plaid Integration
Route::middleware(['auth:api'])->group(function () {
    Route::post('plaid/create-link-token', [PlaidController::class, 'createLinkToken']);
    Route::post('plaid/exchange-public-token', [PlaidController::class, 'exchangePublicToken']);
    Route::post('plaid/reset-login', [PlaidController::class, 'resetLogin']);
});

// Hosted UI
Route::get('connect-bank', [PlaidController::class, 'showLinkPage']);

// Webhook
use App\Http\Controllers\Zilmoney\PlaidWebhookController;
Route::post('plaid/webhook', [PlaidWebhookController::class, 'handleWebhook']);
