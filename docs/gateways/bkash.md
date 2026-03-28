# bKash Setup Guide (Bangladesh)

## Prerequisites

- bKash merchant account
- bKash Sandbox account (for testing)

## Installation

### 1. Get Credentials

1. Register at [bKash Developer](https://developer.bka.sh/)
2. Create app to get:
   - App Key
   - App Secret
   - Username
   - Password

### 2. Configure Environment

```env
# .env
BKASH_APP_KEY=xxxxx
BKASH_APP_SECRET=xxxxx
BKASH_USERNAME=xxxxx
BKASH_PASSWORD=xxxxx
BKASH_MODE=sandbox  # or 'live'
```

### 3. Update Config

```php
// config/payment.php
'gateways' => [
    'bkash' => [
        'app_key' => env('BKASH_APP_KEY'),
        'app_secret' => env('BKASH_APP_SECRET'),
        'username' => env('BKASH_USERNAME'),
        'password' => env('BKASH_PASSWORD'),
        'mode' => env('BKASH_MODE', 'sandbox'),
    ],
],
```

## Usage Example

```php
$response = Payment::gateway('bkash')->pay([
    'amount' => 1000.00,
    'currency' => 'BDT',
    'email' => 'customer@example.com',
    'return_url' => route('payment.success'),
]);
```

## Webhook Events

- `payment_success`
- `payment_failed`
- `payment_cancel`

## Test Mode

Use sandbox credentials for testing. Simulate payment using bKash test app.

## Resources

- [bKash Developer Portal](https://developer.bka.sh/)
