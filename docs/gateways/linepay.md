# Line Pay Gateway Integration Guide

## Overview

LINE Pay is a mobile payment service integrated with LINE messenger, popular in Japan, Taiwan, Thailand, and Indonesia.

## Configuration

```env
LINEPAY_CHANNEL_ID=your_channel_id
LINEPAY_CHANNEL_SECRET=your_channel_secret
LINEPAY_ENVIRONMENT=sandbox
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// LINE Pay Payment
$response = Payment::gateway('linepay')->pay([
    'amount' => 1000,
    'currency' => 'JPY',
    'order_id' => 'ORDER_' . time(),
    'product_name' => 'Product Name',
    'return_url' => route('payment.success'),
    'confirm_url' => route('payment.confirm'),
]);
```

## Payment Flow

1. Initialize payment → Get payment URL
2. User completes on LINE app
3. Confirm payment → Get result

```php
// Step 1: Initialize
$response = Payment::gateway('linepay')->initialize([
    'amount' => 1000,
    'currency' => 'JPY',
    'order_id' => 'ORDER_' . time(),
    'product_name' => 'Product Name',
    'return_url' => route('payment.success'),
    'confirm_url' => route('payment.confirm'),
]);

// Redirect user to LINE Pay
$paymentUrl = $response->getPaymentUrl();

// Step 2: Confirm (after user returns)
$response = Payment::gateway('linepay')->confirm([
    'transaction_id' => $request->transactionId,
    'amount' => 1000,
]);
```

## Webhook Events

- `PAYMENT_COMPLETED`
- `PAYMENT_FAILED`
- `REFUND_COMPLETED`

## Testing

Use LINE Pay sandbox at https://pay.line.me/
