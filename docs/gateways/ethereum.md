# Ethereum (ETH) Gateway Integration Guide

## Overview

Accept Ethereum and ERC-20 token payments with smart contract support.

## Configuration

```env
ETHEREUM_WALLET_ADDRESS=your_eth_wallet_address
ETHEREUM_WEBHOOK_SECRET=your_webhook_secret
ETHEREUM_NETWORK=sepolia # or mainnet
ETHEREUM_GAS_SETTINGS={"gasLimit": 21000, "maxFeePerGas": "50"}
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// Ethereum Payment
$response = Payment::gateway('ethereum')->pay([
    'amount' => 100.00,
    'currency' => 'USD', // Converts to ETH
    'order_id' => 'ORDER_' . time(),
    'callback_url' => route('payment.callback'),
    'return_url' => route('payment.success'),
]);
```

## Crypto Payment

```php
// Accept directly in ETH
$response = Payment::gateway('ethereum')->pay([
    'amount' => 0.05, // In ETH
    'currency' => 'ETH',
    'order_id' => 'ORDER_' . time(),
    'wallet_address' => 'your_eth_wallet',
]);
```

## Get Payment Address

```php
$response = Payment::gateway('ethereum')->createInvoice([
    'amount' => 100.00,
    'currency' => 'USD',
    'order_id' => 'ORDER_' . time(),
]);

$ethAddress = $response->getAddress();
$expectedAmount = $response->getExpectedAmount();
$qrCode = $response->getQrCode();
```

## Token Payments (ERC-20)

```php
$response = Payment::gateway('ethereum')->pay([
    'amount' => 100.00,
    'currency' => 'USD',
    'token' => 'USDT', // or USDC, DAI, etc.
    'order_id' => 'ORDER_' . time(),
    'contract_address' => '0x...', // Token contract address
]);
```

## Webhook Events

- `payment.received` - ETH/tokens received
- `payment.confirmed` - Sufficient confirmations
- `payment.failed` - Transaction failed

## Testing

Use Sepolia or Rinkeby testnet for sandbox testing.
