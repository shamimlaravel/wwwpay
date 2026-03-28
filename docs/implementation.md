# WwwPay - Implementation Guide

A comprehensive guide for implementing WwwPay in your Laravel application.

## Table of Contents

1. [Installation](#installation)
2. [Configuration](#configuration)
3. [Basic Usage](#basic-usage)
4. [Advanced Features](#advanced-features)
5. [Webhook Setup](#webhook-setup)
6. [Testing](#testing)
7. [Deployment](#deployment)

---

## Installation

### Prerequisites

- PHP 8.1 or higher
- Laravel 9.0 or higher
- Required PHP extensions: bcmath, json

### Step 1: Install via Composer

```bash
composer require shamimstack/wwwpay
```

### Step 2: Interactive Setup

```bash
php artisan payment:install
```

This will:
- Publish configuration files
- Create database migrations
- Help configure environment variables

### Step 3: Manual Configuration

If you prefer manual setup:

```bash
# Publish config
php artisan vendor:publish --provider="ShamimStack\WwwPay\Providers\PaymentServiceProvider"

# Publish migrations
php artisan vendor:publish --tag="payment-migrations"

# Run migrations
php artisan migrate
```

---

## Configuration

### Environment Variables

Add these to your `.env` file:

```env
# Default Gateway
PAYMENT_DEFAULT_GATEWAY=stripe

# Stripe Configuration
STRIPE_KEY=sk_test_xxxxx
STRIPE_SECRET=sk_test_xxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxx

# PayPal Configuration
PAYPAL_CLIENT_ID=xxxxx
PAYPAL_CLIENT_SECRET=xxxxx
PAYPAL_MODE=sandbox

# Paystack (Africa)
PAYSTACK_PUBLIC_KEY=pk_live_xxxxx
PAYSTACK_SECRET_KEY=sk_live_xxxxx

# bKash (Bangladesh)
BKASH_APP_KEY=xxxxx
BKASH_APP_SECRET=xxxxx
BKASH_USERNAME=xxxxx
BKASH_PASSWORD=xxxxx
```

### Gateway Configuration

All gateways can be configured in `config/payment.php`:

```php
'gateways' => [
    'stripe' => [
        'api_key' => env('STRIPE_KEY'),
        'api_secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],
    
    'paystack' => [
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
    ],
    
    // Add more gateways as needed...
],
```

### Currency Settings

```php
'currency' => [
    'base' => env('PAYMENT_BASE_CURRENCY', 'USD'),
    
    'allowed' => [
        'USD', 'EUR', 'GBP', 'BDT', 'NGN', 'INR', 'PKR',
        // Add your supported currencies
    ],
    
    'provider' => 'exchangerate',
    'api_key' => env('PAYMENT_EXCHANGE_RATE_API_KEY'),
],
```

---

## Basic Usage

### The Payment Facade

```php
use ShamimStack\WwwPay\Facades\Payment;
```

### Process a Payment

```php
// With default gateway
$response = Payment::pay([
    'amount' => 100.00,
    'currency' => 'USD',
    'return_url' => route('payment.success'),
    'cancel_url' => route('payment.cancel'),
]);

// With specific gateway
$response = Payment::gateway('paystack')->pay([
    'amount' => 10000.00,  // Paystack uses smallest currency unit
    'currency' => 'NGN',
    'email' => 'customer@example.com',
    'return_url' => route('payment.success'),
]);

if ($response->isSuccessful()) {
    $transactionId = $response->getTransactionId();
    
    // Store transaction and redirect
    return redirect($response->getRedirectUrl());
}

if ($response->isRedirect()) {
    // Redirect to payment page
    return redirect($response->getRedirectUrl());
}

// Payment failed
return back()->withError($response->getErrorMessage());
```

### Handle Payment Response

```php
public function success(Request $request)
{
    $response = Payment::gateway('stripe')->verify($request);
    
    if ($response->isSuccessful()) {
        $transactionId = $response->getTransactionId();
        
        // Update order status, send confirmation email, etc.
        $order = Order::find($request->session()->get('order_id'));
        $order->markAsPaid($transactionId);
        
        return redirect()->route('order.confirmed', $order->id);
    }
    
    return redirect()->route('payment.failed');
}
```

### Process a Refund

```php
// Full refund
$response = Payment::gateway('stripe')->refund($transactionId);

// Partial refund
$response = Payment::gateway('stripe')->refund($transactionId, 50.00);

// With reason
$response = Payment::gateway('stripe')->refund($transactionId, null, [
    'reason' => 'Customer requested',
]);
```

### Subscription Payments

```php
// Create subscription
$subscription = Payment::gateway('stripe')->subscribe([
    'plan_id' => 'price_123',
    'customer_email' => 'customer@example.com',
    'payment_method' => 'pm_card_visa',
]);

if ($subscription->isActive()) {
    // Store subscription ID
    $user->subscription_id = $subscription->getSubscriptionId();
    $user->save();
}

// Cancel subscription
Payment::gateway('stripe')->cancelSubscription($subscriptionId);

// Pause subscription
Payment::gateway('stripe')->pauseSubscription($subscriptionId);
```

---

## Advanced Features

### Multi-Gateway Fallback

```php
public function processPaymentWithFallback(array $data)
{
    $gateways = ['stripe', 'paypal', 'paystack'];
    
    foreach ($gateways as $gateway) {
        try {
            $response = Payment::gateway($gateway)->pay($data);
            
            if ($response->isSuccessful()) {
                return $response;
            }
        } catch (\Exception $e) {
            continue; // Try next gateway
        }
    }
    
    throw new \Exception('All payment gateways failed');
}
```

### Currency Conversion

```php
use ShamimStack\WwwPay\Helpers\CurrencyConverter;

$converter = new CurrencyConverter('USD');

// Convert amount
$eurAmount = $converter->convert(100.00, 'USD', 'EUR');

// Get exchange rate
$rate = $converter->getRate('USD', 'EUR');

// Format for display
$formatted = $converter->format(100.00, 'EUR');
// Output: "€100.00"
```

### B2B Payments

```php
use ShamimStack\WwwPay\B2B\B2BPayment;

// Create Invoice
$invoice = B2BPayment::createInvoice([
    'amount' => 1000.00,
    'currency' => 'USD',
    'client_name' => 'Acme Corp',
    'client_email' => 'billing@acme.com',
    'due_date' => now()->addDays(30),
    'items' => [
        ['description' => 'Web Development', 'quantity' => 1, 'price' => 1000],
    ],
]);

// Send Invoice
B2BPayment::sendInvoice($invoice->getInvoiceId());

// Wire Transfer
$transfer = B2BPayment::wireTransfer([
    'amount' => 5000.00,
    'currency' => 'EUR',
    'iban' => 'DE89370400440532013000',
    'bic' => 'COBADEFFXXX',
    'beneficiary_name' => 'Acme GmbH',
]);
```

### P2P Payments

```php
use ShamimStack\WwwPay\P2P\P2PPayment;

// Send Money
$transfer = P2PPayment::sendMoney([
    'amount' => 100.00,
    'currency' => 'USD',
    'recipient_id' => 'user_123',
    'note' => 'For dinner',
]);

// Request Money
$request = P2PPayment::requestMoney([
    'amount' => 50.00,
    'currency' => 'USD',
    'from_user' => 'user_456',
]);

// Split Payment
$split = P2PPayment::splitPayment([
    'total_amount' => 300.00,
    'participants' => ['user_1', 'user_2', 'user_3'],
    'currency' => 'USD',
]);

// Escrow
$escrow = P2PPayment::escrow([
    'amount' => 500.00,
    'currency' => 'USD',
    'released_to' => 'seller_user',
    'released_on' => now()->addDays(7),
]);
```

### Fraud Detection

```php
use ShamimStack\WwwPay\Security\FraudDetection;

$fraud = new FraudDetection();

$result = $fraud->analyze([
    'amount' => 1000.00,
    'card_country' => 'US',
    'ip_country' => 'US',
    'user_id' => $user->id,
    'email' => $user->email,
]);

if ($result->isHighRisk()) {
    // Block transaction
    return back()->withError('Transaction flagged for review');
}

if ($result->getRiskScore() > 50) {
    // Require additional verification
    return redirect()->route('verify.identity');
}
```

### PCI Compliance Helpers

```php
use ShamimStack\WwwPay\Security\PciCompliance;

$pci = new PciCompliance();

// Validate card number (Luhn algorithm)
if (!$pci->validateCardNumber('4242424242424242')) {
    return back()->withError('Invalid card number');
}

// Get card type
$cardType = $pci->detectCardType('4242424242424242');
// Returns: 'visa', 'mastercard', 'amex', etc.

// Validate expiry
if (!$pci->validateExpiry('12', '2025')) {
    return back()->withError('Card has expired');
}

// Validate CVV
if (!$pci->validateCvv('123', $cardType)) {
    return back()->withError('Invalid CVV');
}

// Mask card number for storage
$masked = $pci->maskCardNumber('4242424242424242');
// Output: ************4242

// Hash card for comparison
$hash = $pci->hashCardNumber('4242424242424242');
```

---

## Webhook Setup

### Route Configuration

Add to `routes/web.php`:

```php
use ShamimStack\WwwPay\Http\Controllers\WebhookController;

Route::post('/webhook/{gateway}', [WebhookController::class, 'handle'])
    ->name('payment.webhook');
```

### Verify Webhook Signatures

```php
// Stripe requires raw body for signature verification
Route::post('/webhook/stripe', function (Request $request) {
    return Payment::gateway('stripe')
        ->handleWebhook($request, true)  // true = verify signature
        ? response('OK', 200)
        : response('Invalid', 400);
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
```

### Handle Webhook Events

Create a custom handler in `app/Providers/EventServiceProvider.php`:

```php
protected $listen = [
    \ShamimStack\WwwPay\Events\PaymentSuccessful::class => [
        \App\Listeners\HandlePaymentSuccess::class,
    ],
    \ShamimStack\WwwPay\Events\PaymentFailed::class => [
        \App\Listeners\HandlePaymentFailure::class,
    ],
    \ShamimStack\WwwPay\Events\RefundProcessed::class => [
        \App\Listeners\HandleRefund::class,
    ],
];
```

### Event Listeners

```php
// app/Listeners/HandlePaymentSuccess.php
class HandlePaymentSuccess
{
    public function handle(PaymentSuccessful $event)
    {
        $transaction = $event->transaction;
        
        // Update order status
        $order = Order::findByTransaction($transaction->id);
        $order->markAsPaid();
        
        // Send confirmation email
        Mail::to($order->customer_email)->send(new OrderConfirmation($order));
        
        // Update inventory
        $order->decrementInventory();
    }
}
```

---

## Testing

### Unit Tests

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/Unit/GatewayTest.php

# Run with coverage
vendor/bin/phpunit --coverage-html coverage
```

### Integration Tests

```php
// tests/Integration/PaymentTest.php
class PaymentTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_successful_payment()
    {
        $response = Payment::gateway('stripe')->pay([
            'amount' => 100.00,
            'currency' => 'USD',
            'payment_method' => 'pm_card_visa',
        ]);
        
        $this->assertTrue($response->isSuccessful());
    }
}
```

### Mocking Gateways

```php
public function test_payment_with_mock()
{
    Payment::gateway('stripe')->shouldReceive('pay')
        ->once()
        ->andReturn(new PaymentResponse([
            'status' => 'success',
            'transaction_id' => 'txn_123',
        ]));
    
    $response = Payment::gateway('stripe')->pay([
        'amount' => 100.00,
        'currency' => 'USD',
    ]);
    
    $this->assertTrue($response->isSuccessful());
}
```

### Sandbox Testing

```env
# Enable sandbox/test mode
STRIPE_KEY=sk_test_xxxxx
PAYPAL_MODE=sandbox
PAYSTACK_MODE=test
BKASH_MODE=sandbox
```

---

## Deployment

### Production Checklist

1. **Environment Variables**
   - Use production API keys
   - Enable webhook signatures
   - Set correct mode (live/production)

2. **Security**
   - Use HTTPS everywhere
   - Enable webhook verification
   - Implement IP whitelisting
   - Regular security audits

3. **Monitoring**
   - Set up payment alerts
   - Monitor failed transactions
   - Track webhook delivery

4. **Database**
   - Run migrations
   - Set up proper indexes
   - Configure backups

### Performance Optimization

```php
// Cache gateway configurations
'gateways' => [
    'stripe' => [
        'cache_config' => true,
        'cache_ttl' => 3600,
    ],
],

// Queue webhook processing
'webhook' => [
    'queue' => true,
    'queue_name' => 'payment-webhooks',
],
```

### Error Handling

```php
try {
    $response = Payment::gateway('stripe')->pay($data);
} catch (\ShamimStack\WwwPay\Exceptions\PaymentException $e) {
    Log::error('Payment failed', [
        'gateway' => 'stripe',
        'error' => $e->getMessage(),
        'code' => $e->getCode(),
    ]);
    
    return back()->withError('Payment failed. Please try again.');
}
```

---

## CLI Commands

### List Available Gateways

```bash
php artisan payment:list
php artisan payment:list --region=africa
```

### Test Gateway Connection

```bash
php artisan payment:test stripe
php artisan payment:test paystack --amount=1000 --currency=NGN
```

### Setup Webhooks

```bash
php artisan payment:webhook --gateway=stripe
php artisan payment:webhook --all
```

---

## Support

- **Documentation**: [docs/index.html](docs/index.html)
- **API Reference**: [docs/api-reference.md](docs/api-reference.md)
- **Gateway Guides**: [docs/gateways/](docs/gateways/)
- **Troubleshooting**: [docs/troubleshooting.md](docs/troubleshooting.md)

---

## License

MIT License - see LICENSE file for details.
