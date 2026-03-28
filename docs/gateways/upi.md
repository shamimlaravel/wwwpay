# UPI Gateway Integration Guide

## Overview

Unified Payments Interface (UPI) is India's real-time payment system that enables instant fund transfers between bank accounts on a mobile platform.

## Configuration

```env
UPI_MERCHANT_ID=your_merchant_id
UPI_MERCHANT_KEY=your_merchant_key
UPI_ENVIRONMENT=sandbox
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// Initialize UPI payment
$response = Payment::gateway('upi')->pay([
    'amount' => 1000.00,
    'currency' => 'INR',
    'vpa' => 'customer@upi',  // Virtual Payment Address
    'customer_name' => 'John Doe',
    'return_url' => route('payment.success'),
]);
```

## Payment Flow

1. Customer enters VPA (Virtual Payment Address)
2. Customer authenticates on their banking app
3. Transaction is processed in real-time
4. Payment confirmation received instantly

## Response Handling

```php
if ($response->isSuccessful()) {
    $transactionId = $response->getTransactionId();
    // UPI reference number
}

if ($response->isPending()) {
    // Transaction awaiting confirmation
}
```

## Webhook Events

- `payment.completed` - Successful UPI transfer
- `payment.failed` - Failed transaction
- `payment.pending` - Awaiting confirmation

## Testing

Use test VPAs in sandbox mode:
- `success@upi` - Simulates successful payment
- `failure@upi` - Simulates failed payment
