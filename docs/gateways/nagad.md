# Nagad Gateway Integration Guide

## Overview

Nagad is Bangladesh's leading digital payment service with extensive agent network and mobile app.

## Configuration

```env
NAGAD_MERCHANT_ID=your_merchant_id
NAGAD_MERCHANT_KEY=your_merchant_key
NAGAD_CALLBACK_URL=https://yoursite.com/webhook/nagad
NAGAD_ENVIRONMENT=sandbox
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// Nagad Payment
$response = Payment::gateway('nagad')->pay([
    'amount' => 500.00,
    'currency' => 'BDT',
    'order_id' => 'ORDER_' . time(),
    'customer_msisdn' => '88017XXXXXXXX',
    'return_url' => route('payment.success'),
]);
```

## Payment Flow

```php
// Initialize payment
$response = Payment::gateway('nagad')->initialize([
    'amount' => 500.00,
    'currency' => 'BDT',
    'order_id' => 'ORDER_' . time(),
    'customer_msisdn' => '88017XXXXXXXX',
    'return_url' => route('payment.success'),
]);

// Get payment reference
$paymentRef = $response->getPaymentRef();

// Verify after callback
$response = Payment::gateway('nagad')->verify($request);
```

## Webhook Events

- `Success` - Payment successful
- `Failed` - Payment failed
- `Cancelled` - Payment cancelled

## Testing

Use Nagad sandbox with test merchant credentials.
