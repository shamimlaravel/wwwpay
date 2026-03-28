# GrabPay Gateway Integration Guide

## Overview

GrabPay is a digital wallet and payment method serving Southeast Asia across Singapore, Malaysia, Indonesia, Philippines, Thailand, and Vietnam.

## Configuration

```env
GRABPAY_PARTNER_ID=your_partner_id
GRABPAY_PARTNER_KEY=your_partner_key
GRABPAY_MERCHANT_ID=your_merchant_id
GRABPAY_ENVIRONMENT=sandbox
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// GrabPay Payment
$response = Payment::gateway('grabpay')->pay([
    'amount' => 1000, // In smallest currency unit
    'currency' => 'SGD',
    'order_id' => 'ORDER_' . time(),
    'description' => 'Order Payment',
    'redirect_url' => route('payment.callback'),
]);
```

## Payment Flow

```php
// Initialize payment
$response = Payment::gateway('grabpay')->create([
    'amount' => 1000,
    'currency' => 'SGD',
    'order_id' => 'ORDER_' . time(),
    'description' => 'Order Payment',
]);

// Get payment URL/QR
$paymentData = $response->getPaymentData();
$grabPayUrl = $paymentData['url'] ?? null;

// Confirm payment
$response = Payment::gateway('grabpay')->confirm([
    'transaction_id' => $transactionId,
    'amount' => 1000,
]);
```

## Webhook Events

- `PAYMENT_COMPLETED`
- `PAYMENT_FAILED`
- `REFUND_COMPLETED`

## Supported Countries

Singapore (SGD), Malaysia (MYR), Indonesia (IDR), Philippines (PHP), Thailand (THB), Vietnam (VND)

## Testing

Use GrabPay sandbox at https://developer.grab.com/
