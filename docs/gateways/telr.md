# Telr Gateway Integration Guide

## Overview

Telr is a payment gateway serving the Middle East, South Africa, and India with multi-currency support.

## Configuration

```env
TELR_MERCHANT_ID=your_merchant_id
TELR_STORE_ID=your_store_id
TELR_SECRET_KEY=your_secret_key
TELR_ENVIRONMENT=sandbox
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// Telr Payment
$response = Payment::gateway('telr')->pay([
    'amount' => 100.00,
    'currency' => 'AED',
    'order_id' => 'ORDER_' . time(),
    'description' => 'Order Payment',
    'customer' => [
        'email' => 'customer@example.com',
        'name' => 'John Doe',
        'phone' => '+971501234567',
    ],
    'return_url' => route('payment.success'),
]);
```

## Payment Flow

```php
// Create order
$response = Payment::gateway('telr')->createOrder([
    'amount' => 100.00,
    'currency' => 'AED',
    'order_id' => 'ORDER_' . time(),
    'description' => 'Order Payment',
    'return_url' => route('payment.success'),
    'cancel_url' => route('payment.cancel'),
]);

// Redirect to Telr
if ($response->isRedirect()) {
    return redirect($response->getRedirectUrl());
}

// Verify after return
$response = Payment::gateway('telr')->verify($request);
```

## Webhook Events

- `confirmed` - Payment confirmed
- `pending` - Payment pending
- `failed` - Payment failed
- `cancelled` - Payment cancelled

## Supported Countries

UAE, Saudi Arabia, Qatar, Oman, Kuwait, Bahrain, Egypt, Jordan, Lebanon, South Africa, India

## Testing

Use Telr sandbox at https://telr.com/developer/
