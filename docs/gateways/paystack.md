# Paystack Setup Guide (Africa)

## Prerequisites

- Paystack account (Nigeria, Ghana, South Africa)

## Installation

### 1. Get API Keys

1. Go to [Paystack Dashboard](https://dashboard.paystack.co/)
2. Navigate to **Settings → API Keys**
3. Copy keys for test or live mode

### 2. Configure Environment

```env
# .env
PAYSTACK_PUBLIC_KEY=pk_live_xxxxx
PAYSTACK_SECRET_KEY=sk_live_xxxxx
PAYSTACK_MODE=test  # or 'live'
```

### 3. Update Config

```php
// config/payment.php
'gateways' => [
    'paystack' => [
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
    ],
],
```

## Webhook Setup

### 1. Configure in Paystack Dashboard

1. Go to **Settings → Webhooks**
2. Add URL: `https://yourdomain.com/webhook/paystack`

### 2. Add Route

```php
Route::post('/webhook/paystack', function (Request $request) {
    return Payment::gateway('paystack')
        ->handleWebhook($request)
        ? response('OK', 200)
        : response('Invalid', 400);
});
```

## Usage Example

```php
$response = Payment::gateway('paystack')->pay([
    'amount' => 1000000,  // In kobo (1,000,000 = 10,000 NGN)
    'currency' => 'NGN',
    'email' => 'customer@example.com',
    'return_url' => route('payment.success'),
]);

if ($response->isRedirect()) {
    return redirect($response->getRedirectUrl());
}
```

## Test Cards

| Card Number | Scenario |
|-------------|----------|
| `4084084084084081` | Success |
| `4000050050055555` | Success (Visa) |
| `5500000000000004` | Success (Mastercard) |

## Supported Countries

- Nigeria (NGN)
- Ghana (GHS)
- South Africa (ZAR)

## Resources

- [Paystack Documentation](https://paystack.com/docs/)
