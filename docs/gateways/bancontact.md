# Bancontact Gateway Integration Guide

## Overview

Bancontact is Belgium's dominant payment method with over 15 million cards in circulation.

## Configuration

```env
BANCONTACT_MERCHANT_ID=your_merchant_id
BANCONTACT_PRIVATE_KEY=your_private_key
BANCONTACT_ENVIRONMENT=sandbox
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// Bancontact Payment
$response = Payment::gateway('bancontact')->pay([
    'amount' => 10000, // In cents
    'currency' => 'EUR',
    'order_id' => 'ORDER_' . time(),
    'description' => 'Order Payment',
    'return_url' => route('payment.success'),
    'cancel_url' => route('payment.cancel'),
]);
```

## Payment Flow

1. Create payment request
2. Redirect customer to Bancontact
3. Customer authenticates with bank app/card
4. Return to merchant with result

```php
$response = Payment::gateway('bancontact')->pay([
    'amount' => 10000,
    'currency' => 'EUR',
    'order_id' => 'ORDER_' . time(),
    'description' => 'Order Payment',
    'return_url' => route('payment.success'),
]);

if ($response->isRedirect()) {
    return redirect($response->getRedirectUrl());
}
```

## Webhook Events

- `payment_completed`
- `payment_pending`
- `payment_failed`

## Testing

Use test cards from your payment provider's sandbox.
