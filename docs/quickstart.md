# WwwPay - Developer's Quick Start Guide

## 5-Minute Setup

### 1. Install (30 seconds)

```bash
composer require shamimstack/wwwpay
```

### 2. Install Interactive CLI (1 minute)

```bash
php artisan payment:install
```

This wizard will:
- Select your preferred gateways
- Configure environment variables
- Publish config files
- Setup database migrations

### 3. Configure .env (2 minutes)

```env
PAYMENT_DEFAULT_GATEWAY=stripe

# Stripe
STRIPE_KEY=sk_test_xxxxx
STRIPE_SECRET=sk_test_xxxxx

# PayPal  
PAYPAL_CLIENT_ID=xxxxx
PAYPAL_CLIENT_SECRET=xxxxx
```

### 4. Make Your First Payment (2 minutes)

```php
use ShamimStack\WwwPay\Facades\Payment;

$response = Payment::pay([
    'amount' => 99.99,
    'currency' => 'USD',
    'return_url' => url('/checkout/success'),
]);

if ($response->isRedirect()) {
    return redirect($response->getRedirectUrl());
}
```

---

## Common Patterns

### Checkout Flow

```php
// 1. Create order
$order = Order::create([
    'user_id' => auth()->id(),
    'total' => 199.99,
]);

// 2. Initialize payment
$payment = Payment::pay([
    'amount' => $order->total,
    'currency' => 'USD',
    'return_url' => route('checkout.success', $order->id),
    'metadata' => ['order_id' => $order->id],
]);

// 3. Redirect to gateway
return redirect($payment->getRedirectUrl());

// 4. Handle return
$response = Payment::verify(request());

if ($response->isSuccessful()) {
    $order->update(['status' => 'paid']);
    return redirect()->route('order.confirmed', $order);
}
```

### Multiple Gateways

```php
// Try Stripe first, fallback to PayPal
try {
    $response = Payment::gateway('stripe')->pay($data);
} catch (\Exception $e) {
    $response = Payment::gateway('paypal')->pay($data);
}
```

### Subscriptions

```php
// Subscribe user
$subscription = Payment::subscribe([
    'plan_id' => 'price_monthly_9',
    'email' => auth()->user()->email,
]);

if ($subscription->isActive()) {
    auth()->user()->update([
        'subscription_id' => $subscription->getSubscriptionId(),
        'subscription_status' => 'active',
    ]);
}
```

### Refunds

```php
// Full refund
Payment::refund($transactionId);

// Partial refund
Payment::refund($transactionId, 50.00);
```

---

## Regional Gateway Examples

### Bangladesh (bKash)

```php
Payment::gateway('bkash')->pay([
    'amount' => 500.00,
    'currency' => 'BDT',
    'mobile' => '88017XXXXXXXX',
]);
```

### India (UPI)

```php
Payment::gateway('upi')->pay([
    'amount' => 1000.00,
    'currency' => 'INR',
    'vpa' => 'customer@upi',
]);
```

### Africa (Paystack)

```php
Payment::gateway('paystack')->pay([
    'amount' => 50000, // In kobo
    'currency' => 'NGN',
    'email' => 'customer@example.com',
]);
```

### Europe (SEPA)

```php
Payment::gateway('sepa')->pay([
    'amount' => 100.00,
    'currency' => 'EUR',
    'iban' => 'DE89370400440532013000',
    'bic' => 'COBADEFFXXX',
]);
```

---

## Blade Components

```blade
{{-- Simple checkout --}}
<x-payment::checkout-form 
    :amount="100" 
    :currency="'USD'" 
    :gateways="$gateways" 
/>

{{-- Card input only --}}
<x-payment::card-form />

{{-- Status badge --}}
<x-payment::status-badge :status="$transaction->status" />
```

---

## CLI Commands

| Command | Description |
|---------|-------------|
| `payment:install` | Interactive setup wizard |
| `payment:list` | List all gateways |
| `payment:test stripe` | Test gateway connection |
| `payment:webhook` | Setup webhooks |
| `payment:demo` | Generate demo pages |

---

## Error Handling

```php
try {
    $response = Payment::pay($data);
} catch (\ShamimStack\WwwPay\Exceptions\PaymentException $e) {
    Log::error('Payment failed', [
        'message' => $e->getMessage(),
        'gateway' => $e->getGateway(),
    ]);
    
    return back()->withError('Payment failed. Please try again.');
}
```

---

## Need Help?

- Full Documentation: `docs/implementation.md`
- API Reference: `docs/api-reference.md`
- Gateway Guides: `docs/gateways/`
- Troubleshooting: `docs/troubleshooting.md`
