# Easypaisa Gateway Integration Guide

## Overview

Easypaisa is Pakistan's leading mobile banking and payment service.

## Configuration

```env
EASYPAISA_STORE_ID=your_store_id
EASYPAISA_STORE_KEY=your_store_key
EASYPAISA_ENVIRONMENT=sandbox
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// Easypaisa Payment
$response = Payment::gateway('easypaisa')->pay([
    'amount' => 1000.00,
    'currency' => 'PKR',
    'order_id' => 'ORDER_' . time(),
    'customer_mobile' => '03401234567',
    'customer_email' => 'customer@example.com',
    'return_url' => route('payment.success'),
]);
```

## Payment Flow

```php
// Initialize
$response = Payment::gateway('easypaisa')->initialize([
    'amount' => 1000.00,
    'currency' => 'PKR',
    'order_id' => 'ORDER_' . time(),
    'customer_mobile' => '03401234567',
    'return_url' => route('payment.success'),
]);

// Get payment URL
$paymentUrl = $response->getPaymentUrl();

// Verify
$response = Payment::gateway('easypaisa')->verify($request);
```

## Webhook Events

- `Success` - Payment successful
- `Failed` - Payment failed
- `Cancelled` - Payment cancelled

## Testing

Use Easypaisa sandbox with test credentials.
