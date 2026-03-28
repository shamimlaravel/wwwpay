# M-Pesa Gateway Integration Guide

## Overview

M-Pesa is a mobile money service by Safaricom, widely used in Kenya and expanding across Africa.

## Configuration

```env
MPESA_SHORTCODE=your_shortcode
MPESA_PASSKEY=your_passkey
MPESA_CONSUMER_KEY=your_consumer_key
MPESA_CONSUMER_SECRET=your_consumer_secret
MPESA_ENVIRONMENT=sandbox
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// M-Pesa STK Push (Customer-initiated)
$response = Payment::gateway('mpesa')->pay([
    'amount' => 1000, // In KES
    'currency' => 'KES',
    'phone' => '254712345678', // Format: 254XXXXXXXXX
    'account_reference' => 'ORDER_' . time(),
    'transaction_desc' => 'Order Payment',
]);
```

## Payment Flow

1. Customer enters amount and phone number
2. PIN verification on their phone
3. Payment confirmation received

## C2B (Customer to Business)

```php
// Register URL for incoming payments
Payment::gateway('mpesa')->registerUrl([
    'shortcode' => '600000',
    'response_type' => 'Completed',
]);

// Handle C2B payment notification
$response = Payment::gateway('mpesa')->handleC2B($request);
```

## B2C (Business to Customer)

```php
$response = Payment::gateway('mpesa')->disburse([
    'amount' => 5000,
    'phone' => '254712345678',
    'command_id' => 'BusinessPayment',
    'occasion' => 'Salary Payment',
]);
```

## Webhook Events

- `MpesaPaymentSuccess`
- `MpesaPaymentFailed`
- `MpesaTimeout`
- `MpesaRefundSuccess`

## Testing

Use Safaricom's sandbox at https://developer.safaricom.co.ke
