# Moneris Gateway Integration Guide

## Overview

Moneris is Canada's leading payment processor offering credit card and Interac Online payments.

## Configuration

```env
MONERIS_STORE_ID=your_store_id
MONERIS_API_KEY=your_api_key
MONERIS_ENVIRONMENT=sandbox
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// Moneris Payment
$response = Payment::gateway('moneris')->pay([
    'amount' => 100.00,
    'currency' => 'CAD',
    'order_id' => 'ORDER_' . time(),
    'card_number' => '4242424242424242',
    'expiry_month' => '12',
    'expiry_year' => '2025',
    'cvv' => '123',
]);
```

## Card-Present

```php
$response = Payment::gateway('moneris')->pay([
    'amount' => 50.00,
    'currency' => 'CAD',
    'order_id' => 'ORDER_' . time(),
    'card_number' => '4242424242424242',
    'expiry_month' => '12',
    'expiry_year' => '2025',
    'cvv' => '123',
    'card_present' => true,
    'terminal' => '99',
]);
```

## H2H (Host-to-Host)

```php
$response = Payment::gateway('moneris')->pay([
    'amount' => 100.00,
    'currency' => 'CAD',
    'order_id' => 'ORDER_' . time(),
    'crypt_type' => 7, // SSL
    'pan' => '4242424242424242',
    'expdate' => '1225',
]);
```

## Vault/Tokenization

```php
// Create token
$token = Payment::gateway('moneris')->tokenize([
    'card_number' => '4242424242424242',
    'expiry_month' => '12',
    'expiry_year' => '2025',
]);

// Use token for payment
$response = Payment::gateway('moneris')->pay([
    'amount' => 100.00,
    'currency' => 'CAD',
    'order_id' => 'ORDER_' . time(),
    'payment_token' => $token,
]);
```

## Webhook Events

- `purchase` - Payment completed
- `preauth` - Pre-authorization
- `refund` - Refund processed
- `void` - Transaction voided

## Testing

Use Moneris sandbox at https://esqa.moneris.com/
