# Stripe Setup Guide

## Prerequisites

- Stripe account ([signup](https://dashboard.stripe.com/register))
- Laravel 9.0+
- SSL certificate (for production)

## Installation

### 1. Get API Keys

1. Go to [Stripe Dashboard](https://dashboard.stripe.com/apikeys)
2. Copy your API keys:
   - **Test Mode**: `sk_test_xxxxx` and `pk_test_xxxxx`
   - **Live Mode**: `sk_live_xxxxx` and `pk_live_xxxxx`

### 2. Configure Environment

```env
# .env
STRIPE_KEY=pk_test_xxxxx
STRIPE_SECRET=sk_test_xxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxx
```

### 3. Update Config

```php
// config/payment.php
'gateways' => [
    'stripe' => [
        'api_key' => env('STRIPE_KEY'),
        'api_secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],
],
```

## Webhook Setup

### 1. Create Webhook Endpoint

Add to `routes/web.php`:

```php
use ShamimStack\WwwPay\Http\Controllers\WebhookController;

Route::post('/webhook/stripe', [WebhookController::class, 'handle'])
    ->name('payment.webhook.stripe')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
```

### 2. Configure in Stripe Dashboard

1. Go to **Developers → Webhooks**
2. Click **Add endpoint**
3. Enter URL: `https://yourdomain.com/webhook/stripe`
4. Select events:
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
   - `charge.refunded`
5. Copy **Signing secret** to `STRIPE_WEBHOOK_SECRET`

## Test Cards

| Number | Scenario |
|--------|----------|
| `4242424242424242` | Success |
| `4000000000000002` | Always fails |
| `4000002500003155` | Requires authentication |

## Usage Example

```php
// Basic payment
$response = Payment::gateway('stripe')->pay([
    'amount' => 1000,  // in cents
    'currency' => 'USD',
    'payment_method' => 'pm_card_visa',
    'return_url' => route('payment.success'),
]);

// With customer
$response = Payment::gateway('stripe')->pay([
    'amount' => 1000,
    'currency' => 'USD',
    'customer' => 'cus_123',  // Stripe customer ID
    'return_url' => route('payment.success'),
]);
```

## Troubleshooting

| Error | Solution |
|-------|----------|
| `Invalid API Key` | Verify keys in Stripe Dashboard |
| `Webhook Signature Invalid` | Ensure `STRIPE_WEBHOOK_SECRET` is set |
| `Card Declined` | Use test card numbers |
| `Authentication Required` | Enable 3D Secure |

## Resources

- [Stripe Documentation](https://stripe.com/docs)
- [Stripe API Reference](https://stripe.com/docs/api)
- [Stripe Dashboard](https://dashboard.stripe.com)
