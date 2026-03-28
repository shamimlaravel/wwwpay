# PayPal Setup Guide

## Prerequisites

- PayPal Business account
- PayPal Developer account

## Installation

### 1. Get API Credentials

1. Go to [PayPal Developer Dashboard](https://developer.paypal.com/)
2. Navigate to **My Apps & Credentials**
3. Create or select your app
4. Copy:
   - **Client ID**
   - **Secret**

### 2. Configure Environment

```env
# .env
PAYPAL_CLIENT_ID=xxxxx
PAYPAL_CLIENT_SECRET=xxxxx
PAYPAL_MODE=sandbox  # or 'live'
```

### 3. Update Config

```php
// config/payment.php
'gateways' => [
    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'mode' => env('PAYPAL_MODE', 'sandbox'),
    ],
],
```

## Webhook Setup

### 1. Configure Webhook in PayPal Dashboard

1. Go to **My Apps & Credentials**
2. Select your app
3. Add webhook URL: `https://yourdomain.com/webhook/paypal`

### 2. Add Route

```php
Route::post('/webhook/paypal', function (Request $request) {
    return Payment::gateway('paypal')
        ->handleWebhook($request)
        ? response('OK', 200)
        : response('Invalid', 400);
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
```

### 3. Subscribe to Events

- `PAYMENT.CAPTURE.COMPLETED`
- `PAYMENT.CAPTURE.DENIED`
- `CHECKOUT.ORDER.APPROVED`

## Test Accounts

Use PayPal Sandbox accounts:
1. Go to **Developer Dashboard → Sandbox → Accounts**
2. Create Business and Personal accounts
3. Use Business account for receiving, Personal for testing

## Usage Example

```php
$response = Payment::gateway('paypal')->pay([
    'amount' => 100.00,
    'currency' => 'USD',
    'return_url' => route('payment.success'),
    'cancel_url' => route('payment.cancel'),
]);

if ($response->isRedirect()) {
    return redirect($response->getRedirectUrl());
}
```

## Troubleshooting

| Error | Solution |
|-------|----------|
| `Invalid Client ID` | Verify credentials in Developer Dashboard |
| `Redirect URI mismatch` | Add exact URL in PayPal app settings |
| `Payment not completed` | Check PayPal account confirmed |

## Resources

- [PayPal Developer](https://developer.paypal.com/)
- [PayPal REST API](https://developer.paypal.com/docs/api-basics/)
