# PayPay Gateway Integration Guide

## Overview

PayPay is Japan's leading QR code payment service with over 50 million users.

## Configuration

```env
PAYPAY_API_KEY=your_api_key
PAYPAY_API_SECRET=your_api_secret
PAYPAY_MERCHANT_ID=your_merchant_id
PAYPAY_ENVIRONMENT=sandbox
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// PayPay Payment
$response = Payment::gateway('paypay')->pay([
    'amount' => 1000, // In yen
    'currency' => 'JPY',
    'order_id' => 'ORDER_' . time(),
    'description' => 'Product Purchase',
    'callback_url' => route('payment.callback'),
]);
```

## QR Code Payment

```php
$response = Payment::gateway('paypay')->createQrCode([
    'amount' => 1000,
    'currency' => 'JPY',
    'order_id' => 'ORDER_' . time(),
    'description' => 'Order Payment',
]);

$qrCodeUrl = $response->getQrCodeUrl();
$qrCodeImage = $response->getQrCodeImage();
```

## Webhook Events

- `PAYMENT_COMPLETED`
- `PAYMENT_FAILED`
- `REFUND_COMPLETED`

## Testing

Use PayPay sandbox with test merchant credentials.
