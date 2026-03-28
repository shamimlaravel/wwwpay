# WeChat Pay Gateway Integration Guide

## Overview

WeChat Pay is China's second-largest online payment service integrated with WeChat.

## Configuration

```env
WECHAT_MCH_ID=your_merchant_id
WECHAT_API_KEY=your_api_key
WECHAT_APP_ID=your_app_id
WECHAT_NOTIFY_URL=https://yoursite.com/webhook/wechatpay
WECHAT_ENVIRONMENT=sandbox
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// WeChat Pay Payment
$response = Payment::gateway('wechatpay')->pay([
    'amount' => 10000, // In fen (cents)
    'currency' => 'CNY',
    'out_trade_no' => 'ORDER_' . time(),
    'description' => 'Product Purchase',
    'attach' => 'order_data',
]);
```

## Native QR Code

```php
$response = Payment::gateway('wechatpay')->native([
    'amount' => 10000,
    'currency' => 'CNY',
    'out_trade_no' => 'ORDER_' . time(),
    'description' => 'Order Payment',
]);

// Get QR code URL
$qrCodeUrl = $response->getQrCodeUrl();
$qrCodeBase64 = $response->getQrCodeBase64();
```

## H5 Payment (Mobile Web)

```php
$response = Payment::gateway('wechatpay')->h5([
    'amount' => 10000,
    'currency' => 'CNY',
    'out_trade_no' => 'ORDER_' . time(),
    'description' => 'Mobile Order',
    'h5_info' => [
        'type' => 'WAP',
    ],
]);
```

## Webhook Events

- `transaction_id` - Payment successful
- `trade_state` - Trade status update
- `refund` - Refund processed

## Testing

Use WeChat Pay sandbox with test accounts from developer portal.
