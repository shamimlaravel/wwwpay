# Mada Setup Guide (Saudi Arabia)

## Prerequisites

- Mada merchant account
- Point of Sale (POS) terminal (for in-store)
- Online merchant agreement

## Installation

### 1. Get Credentials

Contact Mada to get:
- Merchant ID
- Terminal ID
- Secret Key

### 2. Configure Environment

```env
# .env
MADA_MERCHANT_ID=xxxxx
MADA_TERMINAL_ID=xxxxx
MADA_SECRET_KEY=xxxxx
MADA_MODE=test  # or 'live'
```

### 3. Update Config

```php
// config/payment.php
'gateways' => [
    'mada' => [
        'merchant_id' => env('MADA_MERCHANT_ID'),
        'terminal_id' => env('MADA_TERMINAL_ID'),
        'secret_key' => env('MADA_SECRET_KEY'),
        'mode' => env('MADA_MODE', 'test'),
    ],
],
```

## Usage Example

```php
$response = Payment::gateway('mada')->pay([
    'amount' => 1000.00,
    'currency' => 'SAR',
    'email' => 'customer@example.com',
    'return_url' => route('payment.success'),
]);
```

## Supported Currencies

- SAR (Saudi Riyal)

## Features

- Samsung Pay
- Apple Pay
- Mada card
- Visa/Mastercard

## Resources

- [Mada Merchant Portal](https://merchant.mada.com.sa/)
