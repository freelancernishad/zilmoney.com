<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Zilmoney\DashboardController;
use App\Http\Controllers\Zilmoney\AccountController;
use App\Http\Controllers\Zilmoney\AccountSignatureController;
use App\Http\Controllers\Zilmoney\PayeeController;
use App\Http\Controllers\Zilmoney\PaymentController;
use App\Http\Controllers\Zilmoney\PlaidController;
use App\Http\Controllers\Zilmoney\CardController;
use App\Http\Controllers\Zilmoney\BillController;
use App\Http\Controllers\Zilmoney\PlaidWebhookController;

use App\Http\Controllers\Zilmoney\SignatureSessionController;

// Webhook & Public Signature Sessions
Route::post('plaid/webhook', [PlaidWebhookController::class, 'handleWebhook']);
Route::get('signature-sessions/{token}', [SignatureSessionController::class, 'show']);
Route::post('signature-sessions/{token}/submit', [SignatureSessionController::class, 'submit']);
Route::get('everify/{id}', [PaymentController::class, 'everifyCheck']);
Route::get('outside/payments/{code}', [PaymentController::class, 'getPublicPaymentByCode']);


// Authenticated Routes
Route::middleware([\App\Http\Middleware\AuthenticateUser::class])->group(function () {
    // Signature Sessions (Create Session & Send Email)
    Route::post('signature-sessions', [SignatureSessionController::class, 'store']);
    Route::post('signature-sessions/send-email', [SignatureSessionController::class, 'sendEmail']);
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index']);

    // Cards
    Route::apiResource('cards', CardController::class);

    // Bills
    Route::apiResource('bills', BillController::class);

    // Banking
    Route::post('accounts/validate-routing', [AccountController::class, 'validateRouting']);
    Route::apiResource('accounts', AccountController::class);

    // Account Signatures
    Route::get('accounts/{account}/signatures', [AccountSignatureController::class, 'index']);
    Route::post('accounts/signatures', [AccountSignatureController::class, 'store']);
    Route::put('accounts/signatures/{signature}/primary', [AccountSignatureController::class, 'setPrimary']);
    Route::delete('accounts/signatures/{signature}', [AccountSignatureController::class, 'destroy']);

    // Payees
    Route::post('payees/upload-file', [PayeeController::class, 'uploadFile']);
    Route::get('payees/view-file/{filename}', [PayeeController::class, 'viewFile']);
    Route::apiResource('payees', PayeeController::class);

    // Payments
    Route::get('payments/next-check-number', [PaymentController::class, 'getNextCheckNumberInfo']);
    Route::post('payments/bulk-action', [PaymentController::class, 'bulkAction']);
    Route::post('payments/bulk-store', [PaymentController::class, 'bulkStore']);
    Route::post('payments/blank-checks', [PaymentController::class, 'storeBlankChecks']);
    Route::apiResource('payments', PaymentController::class);
    Route::get('payments/{id}/pdf', [PaymentController::class, 'downloadPdf']);
    Route::post('payments/{id}/email', [PaymentController::class, 'sendEmail']);

    // Deposit Slips
    Route::apiResource('deposit-slips', \App\Http\Controllers\Zilmoney\DepositSlipController::class);
    Route::get('deposit-slips/{id}/pdf', [\App\Http\Controllers\Zilmoney\DepositSlipController::class, 'downloadPdf']);

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

    // Payments
    Route::post('payments/{id}/void', [PaymentController::class, 'voidPayment']);

    // Plaid Integration & Compliance
    Route::post('plaid/create-link-token', [PlaidController::class, 'createLinkToken']);
    Route::post('plaid/exchange-public-token', [PlaidController::class, 'exchangePublicToken']);
    Route::post('plaid/reset-login', [PlaidController::class, 'resetLogin']);
    Route::post('plaid/disconnect', [PlaidController::class, 'disconnectItem']);
    Route::post('plaid/delete-banking-data', [PlaidController::class, 'deleteBankingData']);
    Route::post('plaid/sandbox/create-transaction', [PlaidController::class, 'createSandboxTransaction']);
    Route::post('plaid/sandbox/fire-webhook', [PlaidController::class, 'fireSandboxWebhook']);
    Route::post('plaid/sandbox/transactions', [PlaidController::class, 'getTransactions']);
    Route::get('plaid/sandbox/logs', [PlaidController::class, 'getSandboxLogs']);

    // Hosted UI
    Route::get('connect-bank', [PlaidController::class, 'showLinkPage']);
});
