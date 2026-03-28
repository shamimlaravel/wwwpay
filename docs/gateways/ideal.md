# iDEAL Gateway Integration Guide

## Overview

iDEAL is the most popular online payment method in the Netherlands, directly integrated with Dutch banks.

## Configuration

```env
IDEAL_MERCHANT_ID=your_merchant_id
IDEAL_SUB_ID=0
IDEAL_CERTIFICATE_PATH=/path/to/certificate.crt
IDEAL_PRIVATE_KEY_PATH=/path/to/private.key
IDEAL_ENVIRONMENT=sandbox
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// iDEAL Payment
$response = Payment::gateway('ideal')->pay([
    'amount' => 100.00,
    'currency' => 'EUR',
    'description' => 'Order #12345',
    'return_url' => route('payment.success'),
    'cancel_url' => route('payment.cancel'),
]);
```

## Bank Selection

iDEAL supports 10 Dutch banks:

```php
$response = Payment::gateway('ideal')->pay([
    'amount' => 100.00,
    'currency' => 'EUR',
    'issuer_id' => 'ABN_AMRO', // Optional: preselect bank
]);
```

Available issuers:
- ABN_AMRO
- ASN_BANK
- BUNQ
- ING
- KNAB
- Moneyou
- NordLB
- Rabobank
- RegioBank
- SNS
- Triodos Bank
- Van Lanschot

## Payment Status

```php
$status = Payment::gateway('ideal')->checkStatus($transactionId);

switch ($status->getStatus()) {
    case 'pending':
        // Awaiting bank confirmation
        break;
    case 'success':
        // Payment completed
        break;
    case 'failed':
        // Payment failed
        break;
}
```

## Webhook Events

- `ideal.payment.completed`
- `ideal.payment.failed`
- `ideal.payment.cancelled`

## Testing

Use the test issuer IDs from your payment provider's documentation.
