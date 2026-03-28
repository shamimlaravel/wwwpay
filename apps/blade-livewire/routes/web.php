<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StoreController;
use ShamimStack\WwwPay\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [StoreController::class, 'index'])->name('home');
Route::get('/checkout', [StoreController::class, 'checkout'])->name('payment.checkout');
Route::get('/payment/success', [StoreController::class, 'paymentSuccess'])->name('payment.success');
Route::get('/payment/cancel', [StoreController::class, 'paymentCancel'])->name('payment.cancel');

// Demo pages
Route::get('/payment/subscription', [StoreController::class, 'subscription'])->name('payment.subscription');
Route::get('/payment/p2p', [StoreController::class, 'p2p'])->name('payment.p2p');
Route::get('/payment/b2b', [StoreController::class, 'b2b'])->name('payment.b2b');
Route::get('/payment/demo', [StoreController::class, 'index'])->name('payment.demo');

// Orders
Route::get('/orders', [StoreController::class, 'orders'])->name('orders')->middleware('auth');

// Webhook
Route::post('/webhook/{gateway}', [WebhookController::class, 'handle'])->name('payment.webhook');
