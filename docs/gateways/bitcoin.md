# Bitcoin (BTC) Gateway Integration Guide

## Overview

Accept Bitcoin payments directly to your wallet with automatic conversion options.

## Configuration

```env
BITCOIN_WALLET_ADDRESS=your_bitcoin_wallet_address
BITCOIN_WEBHOOK_SECRET=your_webhook_secret
BITCOIN_NETWORK=testnet # or mainnet
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// Bitcoin Payment
$response = Payment::gateway('bitcoin')->pay([
    'amount' => 100.00,
    'currency' => 'USD', // Converts to BTC
    'order_id' => 'ORDER_' . time(),
    'callback_url' => route('payment.callback'),
    'return_url' => route('payment.success'),
]);
```

## Crypto Payment

```php
// Accept directly in BTC
$response = Payment::gateway('bitcoin')->pay([
    'amount' => 0.0025, // In BTC
    'currency' => 'BTC',
    'order_id' => 'ORDER_' . time(),
    'wallet_address' => 'your_btc_wallet',
]);
```

## Get Payment Address

```php
$response = Payment::gateway('bitcoin')->createInvoice([
    'amount' => 100.00,
    'currency' => 'USD',
    'order_id' => 'ORDER_' . time(),
]);

$bitcoinAddress = $response->getAddress();
$expectedAmount = $response->getExpectedAmount();
$qrCode = $response->getQrCode();
```

## Webhook Events

- `payment.received` - BTC received
- `payment.confirmed` - Sufficient confirmations
- `payment.overpaid` - Amount exceeded
- `payment.underpaid` - Amount below expected

## Confirmation Requirements

```php
$response = Payment::gateway('bitcoin')->pay([
    'amount' => 100.00,
    'currency' => 'USD',
    'confirmations_required' => 3, // Default: 3
]);
```

## Testing

Use testnet addresses for sandbox testing.
