<?php

use Illuminate\Support\Facades\Route;
use ShamimStack\WwwPay\Http\Controllers\Api\PaymentController;
use ShamimStack\WwwPay\Http\Controllers\Api\SubscriptionController;
use ShamimStack\WwwPay\Http\Controllers\Api\B2BController;
use ShamimStack\WwwPay\Http\Controllers\Api\P2PController;
use ShamimStack\WwwPay\Http\Controllers\Api\WebhookController;

Route::prefix('payment')->group(function () {
    Route::get('/', [PaymentController::class, 'index']);
    Route::post('/', [PaymentController::class, 'store']);
    Route::get('/verify', [PaymentController::class, 'verify']);
    Route::get('/{transactionId}', [PaymentController::class, 'show']);
    Route::post('/refund', [PaymentController::class, 'refund']);
});

Route::prefix('subscription')->group(function () {
    Route::get('/', [SubscriptionController::class, 'index']);
    Route::post('/', [SubscriptionController::class, 'store']);
    Route::get('/{subscriptionId}', [SubscriptionController::class, 'show']);
    Route::patch('/{subscriptionId}', [SubscriptionController::class, 'update']);
    Route::delete('/{subscriptionId}', [SubscriptionController::class, 'cancel']);
    Route::post('/{subscriptionId}/pause', [SubscriptionController::class, 'pause']);
    Route::post('/{subscriptionId}/resume', [SubscriptionController::class, 'resume']);
});

Route::prefix('b2b')->group(function () {
    Route::post('/invoice', [B2BController::class, 'createInvoice']);
    Route::post('/invoice/{invoiceId}/send', [B2BController::class, 'sendInvoice']);
    Route::post('/wire-transfer', [B2BController::class, 'wireTransfer']);
    Route::post('/purchase-order', [B2BController::class, 'purchaseOrder']);
});

Route::prefix('p2p')->group(function () {
    Route::post('/send', [P2PController::class, 'sendMoney']);
    Route::post('/request', [P2PController::class, 'requestMoney']);
    Route::post('/split', [P2PController::class, 'splitPayment']);
    Route::post('/escrow', [P2PController::class, 'createEscrow']);
    Route::post('/escrow/{escrowId}/release', [P2PController::class, 'releaseEscrow']);
});

Route::prefix('gateways')->group(function () {
    Route::get('/', [PaymentController::class, 'gateways']);
    Route::get('/{gateway}/test', [PaymentController::class, 'testGateway']);
});

Route::prefix('webhook')->group(function () {
    Route::post('/{gateway}', [WebhookController::class, 'handle']);
});
