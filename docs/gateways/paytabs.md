# PayTabs Gateway Integration Guide

## Overview

PayTabs is a payment gateway serving the Middle East and North Africa region with local payment methods.

## Configuration

```env
PAYTABS_PROFILE_ID=your_profile_id
PAYTABS_SERVER_KEY=your_server_key
PAYTABS_CLIENT_KEY=your_client_key
PAYTABS_ENVIRONMENT=sandbox
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// PayTabs Payment
$response = Payment::gateway('paytabs')->pay([
    'amount' => 100.00,
    'currency' => 'AED', // or SAR, EGP, etc.
    'order_id' => 'ORDER_' . time(),
    'customer_email' => 'customer@example.com',
    'customer_phone' => '+971501234567',
    'return_url' => route('payment.success'),
]);
```

## Payment Methods

- Credit/Debit Cards
- Apple Pay
- Samsung Pay
- valu (Egypt)
- Post Pay
- Bank Installments

## 2-Click Payment

```php
$response = Payment::gateway('paytabs')->pay([
    'amount' => 100.00,
    'currency' => 'AED',
    'order_id' => 'ORDER_' . time(),
    'customer_email' => 'customer@example.com',
    'customer_phone' => '+971501234567',
    'tokenization' => true, // Enable tokenization
    'return_url' => route('payment.success'),
]);
```

## Recurring Payments

```php
$response = Payment::gateway('paytabs')->recurring([
    'amount' => 50.00,
    'currency' => 'AED',
    'recurring_frequency' => 'monthly',
    'recurring_count' => 12,
    'customer_email' => 'customer@example.com',
]);
```

## Webhook Events

- `payment_completed`
- `payment_pending`
- `payment_failed`
- `refund_completed`

## Supported Countries

UAE, Saudi Arabia, Egypt, Oman, Qatar, Kuwait, Bahrain, Jordan, Lebanon

## Testing

Use PayTabs sandbox at https://sdk-incubator.paytabs.com/
