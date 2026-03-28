# Flutterwave Setup Guide (Africa)

## Prerequisites

- Flutterwave account

## Installation

### 1. Get API Keys

1. Go to [Flutterwave Dashboard](https://dashboard.flutterwave.com/)
2. Navigate to **Settings → API Keys**
3. Copy public key and encryption key

### 2. Configure Environment

```env
# .env
FLUTTERWAVE_PUBLIC_KEY=pk_live_xxxxx
FLUTTERWAVE_ENCRYPTION_KEY=xxxxx
FLUTTERWAVE_MODE=sandbox  # or 'live'
```

### 3. Update Config

```php
// config/payment.php
'gateways' => [
    'flutterwave' => [
        'public_key' => env('FLUTTERWAVE_PUBLIC_KEY'),
        'encryption_key' => env('FLUTTERWAVE_ENCRYPTION_KEY'),
    ],
],
```

## Webhook Setup

### 1. Configure in Flutterwave Dashboard

1. Go to **Settings → Webhooks**
2. Add URL: `https://yourdomain.com/webhook/flutterwave`

### 2. Add Route

```php
Route::post('/webhook/flutterwave', function (Request $request) {
    return Payment::gateway('flutterwave')
        ->handleWebhook($request)
        ? response('OK', 200)
        : response('Invalid', 400);
});
```

## Usage Example

```php
$response = Payment::gateway('flutterwave')->pay([
    'amount' => 1000.00,
    'currency' => 'NGN',
    'email' => 'customer@example.com',
    'name' => 'John Doe',
    'return_url' => route('payment.success'),
]);
```

## Supported Countries & Currencies

- Nigeria (NGN)
- Ghana (GHS)
- Kenya (KES)
- Uganda (UGX)
- Tanzania (TZS)
- Zambia (ZMW)

## Resources

- [Flutterwave Documentation](https://developer.flutterwave.com/docs/)
