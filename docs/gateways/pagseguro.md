# PagSeguro Gateway Integration Guide

## Overview

PagSeguro is Brazil's leading payment platform offering credit cards,boleto, and PIX.

## Configuration

```env
PAGSEGURO_EMAIL=your_email
PAGSEGURO_TOKEN=your_token
PAGSEGURO_ENVIRONMENT=sandbox
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// PagSeguro Payment
$response = Payment::gateway('pagseguro')->pay([
    'amount' => 100.00,
    'currency' => 'BRL',
    'order_id' => 'ORDER_' . time(),
    'customer' => [
        'email' => 'customer@example.com',
        'name' => 'João Silva',
    ],
]);
```

## Credit Card

```php
$response = Payment::gateway('pagseguro')->pay([
    'amount' => 100.00,
    'currency' => 'BRL',
    'order_id' => 'ORDER_' . time(),
    'payment_method' => 'credit_card',
    'customer' => [
        'email' => 'customer@example.com',
        'name' => 'João Silva',
        'cpf' => '12345678901',
    ],
    'card' => [
        'token' => $cardToken, // From PagSeguro.js
        'cvv' => '123',
    ],
    'installments' => 1,
]);
```

## Boleto

```php
$response = Payment::gateway('pagseguro')->pay([
    'amount' => 100.00,
    'currency' => 'BRL',
    'order_id' => 'ORDER_' . time(),
    'payment_method' => 'boleto',
    'customer' => [
        'email' => 'customer@example.com',
        'name' => 'João Silva',
        'cpf' => '12345678901',
    ],
]);

$boletoUrl = $response->getBoletoUrl();
$boletoBarcode = $response->getBoletoBarcode();
```

## PIX

```php
$response = Payment::gateway('pagseguro')->pay([
    'amount' => 100.00,
    'currency' => 'BRL',
    'order_id' => 'ORDER_' . time(),
    'payment_method' => 'pix',
    'customer' => [
        'email' => 'customer@example.com',
    ],
]);

$pixQrCode = $response->getPixQrCode();
$pixQrCodeBase64 = $response->getPixQrCodeBase64();
```

## Webhook Events

- `transaction` - Payment update
- `notification` - Status notification

## Testing

Use PagSeguro sandbox credentials and test cards from their developer portal.
