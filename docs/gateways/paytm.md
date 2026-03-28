# Paytm Gateway Integration Guide

## Overview

Paytm is India's leading digital payments platform offering wallet, UPI, and banking integrations.

## Configuration

```env
PAYTM_MERCHANT_ID=your_merchant_id
PAYTM_MERCHANT_KEY=your_merchant_key
PAYTM_WEBSITE=WEBSTAGING
PAYTM_CHANNEL_ID=WAP
PAYTM_ENVIRONMENT=sandbox
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// Paytm Checkout
$response = Payment::gateway('paytm')->pay([
    'amount' => 1000.00,
    'currency' => 'INR',
    'email' => 'customer@example.com',
    'mobile' => '9876543210',
    'order_id' => 'ORDER_' . time(),
]);
```

## Payment Methods

- Paytm Wallet
- UPI
- Net Banking
- Debit Card
- Credit Card

## Checksum Generation

Paytm requires checksum generation for security:

```php
$response = Payment::gateway('paytm')->generateChecksum($params);
```

## Webhook Events

- `payment.completed`
- `payment.failed`
- `refund.processed`

## Testing

Use Paytm's test credentials from their developer dashboard.
