# MercadoPago Gateway Integration Guide

## Overview

MercadoPago is the dominant online payment platform in Latin America, operating in Brazil, Mexico, Argentina, and more.

## Configuration

```env
MERCADO_ACCESS_TOKEN=your_access_token
MERCADO_PUBLIC_KEY=your_public_key
MERCADO_ENVIRONMENT=sandbox
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// MercadoPago Checkout
$response = Payment::gateway('mercadopago')->pay([
    'amount' => 100.00,
    'currency' => 'BRL', // or MXN, ARS, etc.
    'description' => 'Product Purchase',
    'payment_method_id' => 'pix', // or 'bolbradesco', 'master', etc.
    'payer' => [
        'email' => 'customer@email.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
    ],
]);
```

## Payment Methods

- **Brazil**: PIX, Boleto, Credit/Debit Cards, Mercado Pago Wallet
- **Mexico**: OXXO, SPEI, Credit/Debit Cards
- **Argentina**: RapiPago, Pago Fácil, Credit/Debit Cards

## PIX Payment (Brazil)

```php
$response = Payment::gateway('mercadopago')->pay([
    'amount' => 100.00,
    'currency' => 'BRL',
    'payment_method_id' => 'pix',
    'payer' => ['email' => 'customer@example.com'],
]);

// Get PIX QR code
$qrCode = $response->getQrCode();
$qrCodeBase64 = $response->getQrCodeBase64();
```

## Boleto (Brazil)

```php
$response = Payment::gateway('mercadopago')->pay([
    'amount' => 100.00,
    'currency' => 'BRL',
    'payment_method_id' => 'bolbradesco',
    'payer' => [
        'email' => 'customer@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'identification' => [
            'type' => 'CPF',
            'number' => '12345678909',
        ],
    ],
]);

$boletoUrl = $response->getBoletoUrl();
```

## Webhook Events

- `payment.created`
- `payment.updated`
- `payment.approved`
- `payment.rejected`
- `refund.created`

## Testing

Use test users and cards from MercadoPago developer dashboard.
