# Fawry Gateway Integration Guide

## Overview

Fawry is Egypt's leading e-payment network offering various payment channels.

## Configuration

```env
FAWRY_MERCHANT_CODE=your_merchant_code
FAWRY_SECRET_KEY=your_secret_key
FAWRY_ENVIRONMENT=sandbox
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// Fawry Payment
$response = Payment::gateway('fawry')->pay([
    'amount' => 500.00,
    'currency' => 'EGP',
    'merchant_ref_id' => 'ORDER_' . time(),
    'customer' => [
        'name' => 'Ahmed Mohamed',
        'email' => 'ahmed@example.com',
        'mobile' => '01001234567',
    ],
    'description' => 'Order Payment',
]);
```

## Payment Methods

- Fawry Card
- Bank Cards (Visa, Mastercard)
- Mobile Wallet (Fawry, Vale)
- Bank Installments
- Cash Payment (At Fawry outlets)

## Charge Items

```php
$response = Payment::gateway('fawry')->pay([
    'amount' => 1000.00,
    'currency' => 'EGP',
    'merchant_ref_id' => 'ORDER_' . time(),
    'customer' => [
        'name' => 'Ahmed Mohamed',
        'email' => 'ahmed@example.com',
        'mobile' => '01001234567',
    ],
    'items' => [
        [
            'item_id' => 'PROD001',
            'description' => 'Product Name',
            'price' => 500.00,
            'quantity' => 2,
        ],
    ],
]);
```

## Webhook Events

- `PAYMENT_COMPLETED`
- `PAYMENT_REFUNDED`
- `ORDER_EXPIRED`

## Testing

Use Fawry sandbox with test merchant credentials.
