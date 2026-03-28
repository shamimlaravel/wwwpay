# Alipay Gateway Integration Guide

## Overview

Alipay is China's leading online payment platform with over 1 billion active users.

## Configuration

```env
ALIPAY_APP_ID=your_app_id
ALIPAY_PRIVATE_KEY=your_private_key
ALIPAY_PUBLIC_KEY=alipay_public_key
ALIPAY_NOTIFY_URL=https://yoursite.com/webhook/alipay
ALIPAY_ENVIRONMENT=sandbox
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// Alipay Payment
$response = Payment::gateway('alipay')->pay([
    'amount' => 100.00,
    'currency' => 'CNY',
    'out_trade_no' => 'ORDER_' . time(),
    'subject' => 'Product Purchase',
    'return_url' => route('payment.success'),
]);
```

## Payment Methods

- QR Code (Web/WAP)
- Face-to-Face (F2F)
- App Integration

## QR Code Payment

```php
$response = Payment::gateway('alipay')->qrCode([
    'amount' => 100.00,
    'currency' => 'CNY',
    'out_trade_no' => 'ORDER_' . time(),
    'subject' => 'Order Payment',
]);
// Returns QR code image URL
$qrCodeUrl = $response->getQrCodeUrl();
```

## Webhook Events

- `trade_status.TRADE_FINISHED`
- `trade_status.TRADE_SUCCESS`
- `trade_status.TRADE_CLOSED`

## Testing

Use Alipay's sandbox environment with test accounts.
