# PhonePe Gateway Integration Guide

## Overview

PhonePe is India's leading UPI-based payment app with wide merchant acceptance.

## Configuration

```env
PHONEPE_MERCHANT_ID=your_merchant_id
PHONEPE_MERCHANT_KEY=your_merchant_key
PHONEPE_ENVIRONMENT=sandbox
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// PhonePe Payment
$response = Payment::gateway('phonepe')->pay([
    'amount' => 1000, // In paise
    'currency' => 'INR',
    'order_id' => 'ORDER_' . time(),
    'customer_phone' => '9876543210',
    'return_url' => route('payment.success'),
]);
```

## UPI Intent

```php
// Deep link for PhonePe app
$response = Payment::gateway('phonepe')->intent([
    'amount' => 1000,
    'currency' => 'INR',
    'order_id' => 'ORDER_' . time(),
    'customer_phone' => '9876543210',
]);

$deepLink = $response->getDeepLink();
```

## Check Status

```php
$status = Payment::gateway('phonepe')->checkStatus($orderId);

switch ($status->getState()) {
    case 'SUCCESS':
        // Payment successful
        break;
    case 'PENDING':
        // Awaiting confirmation
        break;
    case 'FAILED':
        // Payment failed
        break;
}
```

## Webhook Events

- `SUCCESS` - Payment successful
- `PENDING` - Payment pending
- `FAILED` - Payment failed

## Testing

Use PhonePe sandbox at https://developer.phonepe.com/
