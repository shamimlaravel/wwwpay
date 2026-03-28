# Adyen Gateway Integration Guide

## Overview

Adyen is a global payment platform offering card payments, local payment methods, and risk management.

## Configuration

```env
ADYEN_API_KEY=your_api_key
ADYEN_MERCHANT_ACCOUNT=your_merchant_account
ADYEN_CLIENT_KEY=your_client_key
ADYEN_ENVIRONMENT=sandbox
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// Adyen Payment
$response = Payment::gateway('adyen')->pay([
    'amount' => 10000, // In minor units (cents)
    'currency' => 'USD',
    'reference' => 'ORDER_' . time(),
    'return_url' => route('payment.success'),
]);
```

## Payment Methods

Adyen supports 100+ payment methods globally:

```php
// Get available payment methods
$methods = Payment::gateway('adyen')->getPaymentMethods([
    'countryCode' => 'US',
    'shopperLocale' => 'en-US',
    'amount' => [
        'value' => 10000,
        'currency' => 'USD',
    ],
]);
```

## Card Payments

```php
$response = Payment::gateway('adyen')->pay([
    'amount' => 10000,
    'currency' => 'USD',
    'payment_method' => [
        'type' => 'scheme',
        'number' => '4111111111111111',
        'expiry_month' => '03',
        'expiry_year' => '2030',
        'cvc' => '737',
        'holder_name' => 'John Smith',
    ],
]);
```

## 3D Secure

```php
$response = Payment::gateway('adyen')->pay([
    'amount' => 10000,
    'currency' => 'USD',
    'payment_method' => $cardData,
    'return_url' => route('payment.threeds2'),
    'additional_data' => [
        'executeThreeD' => true,
    ],
]);
```

## Webhook Events

- `authorisation` - Payment authorization
- `capture` - Capture completed
- `refund` - Refund processed
- `cancellation` - Payment cancelled
- `refused` - Payment refused

## Webhook Verification

```php
// Verify Adyen webhook signature
Route::post('/webhook/adyen', function (Request $request) {
    return Payment::gateway('adyen')
        ->handleWebhook($request, true)
        ? response('OK', 200)
        : response('ERROR', 400);
});
```
