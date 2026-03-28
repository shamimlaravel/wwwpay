# JazzCash Gateway Integration Guide

## Overview

JazzCash is Pakistan's leading mobile payment service for wallet and banking transactions.

## Configuration

```env
JAZZCASH_MERCHANT_ID=your_merchant_id
JAZZCASH_PASSWORD=your_password
JAZZCASH_INTEGRITY_SALT=your_salt
JAZZCASH_ENVIRONMENT=sandbox
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// JazzCash Payment
$response = Payment::gateway('jazzcash')->pay([
    'amount' => 1000.00,
    'currency' => 'PKR',
    'mobile' => '923001234567',
    'cnic' => '1234567890123', // CNIC required
    'email' => 'customer@example.pk',
    'return_url' => route('payment.success'),
]);
```

## Payment Methods

- JazzCash Wallet
- Debit Card
- Bank Transfer

## Mobile Money Flow

```php
// For wallet payments
$response = Payment::gateway('jazzcash')->pay([
    'amount' => 500.00,
    'currency' => 'PKR',
    'mobile' => '923001234567',
    'method' => 'wallet',
]);
```

## Response Handling

```php
if ($response->isSuccessful()) {
    $transactionId = $response->getTransactionId();
    $jazzcashRef = $response->getJazzcashRef();
}

if ($response->isPending()) {
    // OTP verification required
    $verificationCode = $response->getVerificationCode();
}
```

## Webhook Events

- `jazzcash.payment.completed`
- `jazzcash.payment.failed`
- `jazzcash.refund.processed`

## Testing

Test in sandbox with Pakistani mobile numbers starting with `0300-0350`.
