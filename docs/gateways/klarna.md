# Klarna Gateway Integration Guide

## Overview

Klarna offers buy now, pay later (BNPL) solutions with installments and pay later options.

## Configuration

```env
KLARNA_USERNAME=your_username
KLARNA_PASSWORD=your_password
KLARNA_ENVIRONMENT=sandbox
KLARNA_REGION=eu
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// Klarna Checkout
$response = Payment::gateway('klarna')->pay([
    'amount' => 100.00,
    'currency' => 'EUR',
    'order_lines' => [
        [
            'type' => 'physical',
            'name' => 'Product Name',
            'quantity' => 1,
            'unit_price' => 10000, // In cents
            'tax_rate' => 2500, // 25%
            'total_amount' => 10000,
        ],
    ],
    'return_url' => route('payment.success'),
    'cancel_url' => route('payment.cancel'),
]);
```

## Klarna Widget

```php
// Initialize Klarna Checkout widget
$session = Payment::gateway('klarna')->createSession([
    'locale' => 'en-US',
    'purchase_country' => 'US',
]);
```

## Payment Methods

- Pay Later (30 days)
- Slice It (installments)
- Pay Now (instant)

## Order Management

```php
// Update order
Payment::gateway('klarna')->updateOrder($klarnaOrderId, [
    'order_lines' => $newOrderLines,
]);

// Cancel order
Payment::gateway('klarna')->cancelOrder($klarnaOrderId);
```

## Webhook Events

- `klarna.order.created`
- `klarna.order.completed`
- `klarna.order.cancelled`
- `klarna.fraud.rated`

## Testing

Use Klarna's test credentials for sandbox testing.
