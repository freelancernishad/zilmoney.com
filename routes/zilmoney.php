<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Zilmoney\DashboardController;
use App\Http\Controllers\Zilmoney\AccountController;
use App\Http\Controllers\Zilmoney\AccountSignatureController;
use App\Http\Controllers\Zilmoney\PayeeController;
use App\Http\Controllers\Zilmoney\PaymentController;
use App\Http\Controllers\Zilmoney\PlaidController;

// Dashboard
Route::get('dashboard', [DashboardController::class, 'index']);

// Banking
Route::post('accounts/validate-routing', [AccountController::class, 'validateRouting']);
Route::apiResource('accounts', AccountController::class);

// Account Signatures
Route::get('accounts/{account}/signatures', [AccountSignatureController::class, 'index']);
Route::post('accounts/signatures', [AccountSignatureController::class, 'store']);
Route::put('accounts/signatures/{signature}/primary', [AccountSignatureController::class, 'setPrimary']);
Route::delete('accounts/signatures/{signature}', [AccountSignatureController::class, 'destroy']);

// Payees
Route::apiResource('payees', PayeeController::class);

// Payments
Route::apiResource('payments', PaymentController::class);
Route::get('payments/{id}/pdf', [PaymentController::class, 'downloadPdf']);

// Payment Categories
Route::apiResource('payment-categories', \App\Http\Controllers\Zilmoney\PaymentCategoryController::class)->only(['index', 'store', 'destroy']);

// Payment Sub-Resources
Route::get('payments/{payment}/logs', [\App\Http\Controllers\Zilmoney\PaymentLogController::class, 'index']);

// Comments
Route::get('payments/{payment}/comments', [\App\Http\Controllers\Zilmoney\PaymentCommentController::class, 'index']);
Route::post('payments/{payment}/comments', [\App\Http\Controllers\Zilmoney\PaymentCommentController::class, 'store']);
Route::delete('payments/{payment}/comments/{comment}', [\App\Http\Controllers\Zilmoney\PaymentCommentController::class, 'destroy']);

// Attachments
Route::get('payments/{payment}/attachments', [\App\Http\Controllers\Zilmoney\PaymentAttachmentController::class, 'index']);
Route::post('payments/{payment}/attachments', [\App\Http\Controllers\Zilmoney\PaymentAttachmentController::class, 'store']);
Route::delete('payments/{payment}/attachments/{attachment}', [\App\Http\Controllers\Zilmoney\PaymentAttachmentController::class, 'destroy']);

// Receipts
Route::get('payments/{payment}/receipts', [\App\Http\Controllers\Zilmoney\PaymentReceiptController::class, 'index']);
Route::post('payments/{payment}/receipts', [\App\Http\Controllers\Zilmoney\PaymentReceiptController::class, 'store']);
Route::delete('payments/{payment}/receipts/{receipt}', [\App\Http\Controllers\Zilmoney\PaymentReceiptController::class, 'destroy']);

// Delivery Proofs
Route::get('payments/{payment}/delivery-proofs', [\App\Http\Controllers\Zilmoney\PaymentDeliveryProofController::class, 'index']);
Route::post('payments/{payment}/delivery-proofs', [\App\Http\Controllers\Zilmoney\PaymentDeliveryProofController::class, 'store']);
Route::delete('payments/{payment}/delivery-proofs/{deliveryProof}', [\App\Http\Controllers\Zilmoney\PaymentDeliveryProofController::class, 'destroy']);

// Remittances
Route::get('payments/{payment}/remittances', [\App\Http\Controllers\Zilmoney\PaymentRemittanceController::class, 'index']);
Route::post('payments/{payment}/remittances', [\App\Http\Controllers\Zilmoney\PaymentRemittanceController::class, 'store']);
Route::delete('payments/{payment}/remittances/{remittance}', [\App\Http\Controllers\Zilmoney\PaymentRemittanceController::class, 'destroy']);

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
