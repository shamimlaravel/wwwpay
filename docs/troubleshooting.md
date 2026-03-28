# WwwPay - Troubleshooting Guide

Common issues and their solutions when using WwwPay.

## Table of Contents

1. [Installation Issues](#installation-issues)
2. [Configuration Issues](#configuration-issues)
3. [Payment Issues](#payment-issues)
4. [Webhook Issues](#webhook-issues)
5. [Gateway-Specific Issues](#gateway-specific-issues)
6. [Testing Issues](#testing-issues)

---

## Installation Issues

### Composer Install Fails

**Problem:** `composer require shamimstack/wwwpay` fails with dependency conflicts.

**Solution:**
```bash
# Clear composer cache
composer clear-cache

# Try with --no-interaction
composer require shamimstack/wwwpay --no-interaction

# If version conflict, specify compatible version
composer require shamimstack/wwwpay:^1.0 --no-interaction
```

---

### Service Provider Not Registered

**Problem:** Payment commands not available.

**Solution:**
```bash
# Manually register in config/app.php
'providers' => [
    // ...
    ShamimStack\WwwPay\Providers\PaymentServiceProvider::class,
],

# Or publish again
php artisan vendor:publish --provider="ShamimStack\WwwPay\Providers\PaymentServiceProvider"
```

---

### Config Not Found

**Problem:** `Config file not found` error.

**Solution:**
```bash
# Publish config
php artisan vendor:publish --provider="ShamimStack\WwwPay\Providers\PaymentServiceProvider" --tag="payment-config"
```

---

## Configuration Issues

### Gateway Not Configured

**Problem:** `Gateway 'xxx' is not configured`

**Solution:**
1. Check `.env` file has required variables:
```env
# Stripe
STRIPE_KEY=sk_live_xxxxx
STRIPE_SECRET=sk_live_xxxxx

# PayPal
PAYPAL_CLIENT_ID=xxxxx
PAYPAL_CLIENT_SECRET=xxxxx
```

2. Verify config in `config/payment.php`:
```php
'gateways' => [
    'stripe' => [
        'api_key' => env('STRIPE_KEY'),
        'api_secret' => env('STRIPE_SECRET'),
    ],
],
```

3. Clear config cache:
```bash
php artisan config:clear
```

---

### Wrong Mode (Sandbox vs Live)

**Problem:** Transactions working in sandbox but failing in production.

**Solution:**
```env
# For production, use live credentials
STRIPE_KEY=sk_live_xxxxx
STRIPE_SECRET=sk_live_xxxxx

# NOT sk_test_xxxxx
```

---

### Missing Currency Support

**Problem:** `Currency XXX not supported by gateway`

**Solution:**
```php
// Check gateway supports your currency
$gateway = Payment::gateway('stripe');
$currencies = $gateway->getSupportedCurrencies();

if (!in_array('BDT', $currencies)) {
    // Use alternative gateway for BDT
    Payment::gateway('bKash')->pay([...]);
}
```

---

## Payment Issues

### Payment Always Fails

**Problem:** Payments fail with generic error.

**Debug Steps:**
```php
try {
    $response = Payment::gateway('stripe')->pay($data);
} catch (\Exception $e) {
    Log::error('Payment failed', [
        'gateway' => 'stripe',
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    
    // Check response
    if (isset($response)) {
        Log::info('Response', $response->getData());
    }
}
```

---

### Card Declined

**Problem:** `Card declined` error.

**Common Causes:**
1. Insufficient funds
2. Card expired
3. Card restricted
4. Fraud detection triggered

**Solution:**
```php
$response = Payment::gateway('stripe')->pay($data);

if (!$response->isSuccessful()) {
    $error = $response->getErrorMessage();
    
    // Check specific decline reason
    if (str_contains($error, 'insufficient_funds')) {
        // Inform user
    }
}
```

---

### 3D Secure Authentication Required

**Problem:** `Authentication required` / `3D Secure`

**Solution:**
```php
$response = Payment::gateway('stripe')->pay([
    'amount' => 100.00,
    'currency' => 'USD',
    'return_url' => route('payment.callback'),
    'payment_method' => 'pm_card_visa',
    'three_d_secure' => true,  // Enable 3DS
]);

if ($response->isRedirect()) {
    // Redirect to 3DS page
    return redirect($response->getRedirectUrl());
}
```

---

### Amount Mismatch

**Problem:** Different amounts between app and gateway.

**Solution:**
```php
// Always use smallest unit for gateways that require it
$amount = 100.00;  // In dollars
$amountInCents = $amount * 100;  // 10000 cents

Payment::gateway('stripe')->pay([
    'amount' => $amountInCents,  // Stripe uses cents
    'currency' => 'USD',
]);

// Paystack uses kobo (cents)
Payment::gateway('paystack')->pay([
    'amount' => $amountInCents,  // Paystack uses kobo
    'currency' => 'NGN',
]);
```

---

## Webhook Issues

### Webhook Not Receiving

**Problem:** Webhook not firing after payment.

**Debug Steps:**
1. Check webhook URL is publicly accessible:
```bash
curl -X POST https://yourapp.com/webhook/stripe -d '{}'
```

2. Verify gateway dashboard has webhook configured:
   - Stripe: Dashboard → Developers → Webhooks
   - PayPal: Developer → Webhooks

3. Check webhook signature verification is working:
```php
Route::post('/webhook/stripe', function (Request $request) {
    // Disable CSRF for webhooks
    return Payment::gateway('stripe')
        ->handleWebhook($request, true)
        ? response('OK', 200)
        : response('Invalid', 400);
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
```

---

### Webhook Signature Invalid

**Problem:** `Webhook signature verification failed`

**Solution:**
```php
// Ensure raw body is used for Stripe
Route::post('/webhook/stripe', function (Request $request) {
    // Stripe needs raw body
    $payload = $request->getContent();
    
    $verified = Payment::gateway('stripe')
        ->verifyWebhookSignature($payload, $request->header('Stripe-Signature'));
    
    return $verified ? response('OK', 200) : response('Invalid', 400);
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
```

---

### Duplicate Webhook Processing

**Problem:** Same webhook event processed multiple times.

**Solution:**
```php
// In your webhook handler
public function handleWebhook(Request $request)
{
    $eventId = $request->header('Stripe-Event-Id');
    
    // Check if already processed
    if (WebhookLog::where('event_id', $eventId)->exists()) {
        return response('Already processed', 200);
    }
    
    // Process webhook
    $this->processWebhook($request);
    
    // Log event
    WebhookLog::create([
        'event_id' => $eventId,
        'processed_at' => now(),
    ]);
    
    return response('OK', 200);
}
```

---

## Gateway-Specific Issues

### Stripe

**`Invalid API Key`**
- Verify keys in Stripe Dashboard
- Check for accidental spaces or newlines

**`No such payment_intent`**
- Transaction ID not found
- Wrong environment (test vs live)

**`Webhook endpoint not found`**
- Webhook URL not configured in Stripe Dashboard
- URL not publicly accessible

---

### PayPal

**`Invalid Client ID`**
- Create app in PayPal Developer Dashboard
- Use correct credentials for mode (sandbox/live)

**`Redirect URI mismatch`**
- Add exact redirect URIs in PayPal app settings
- Include protocol (https://)

---

### Paystack

**`Invalid key`**
- Use test keys for sandbox, live keys for production
- Keys are different for each environment

**`Invalid reference`**
- Transaction reference not found
- Already used reference

---

### bKash

**`Authentication failed`**
- Check app_key, app_secret, username, password
- Token expired - need to re-authenticate

**`Invalid merchant profile`**
- Verify merchant credentials
- Check sandbox vs production mode

---

### Paytm

**`Invalid MID`**
- Use correct Merchant ID from Paytm Dashboard
- Different MID for test vs production

---

## Testing Issues

### Test Payments Not Working

**Problem:** Test transactions fail.

**Solution:**
1. Use test/sandbox credentials, not live
2. Use test card numbers:
   - Stripe: `4242424242424242`
   - PayPal: Use PayPal sandbox accounts
   - Paystack: Use test card numbers

---

### Mocking Not Working

**Problem:** Mock gateway doesn't return expected response.

**Solution:**
```php
// Use Mockery properly
public function test_payment()
{
    Payment::gateway('stripe')->shouldReceive('pay')
        ->once()
        ->with(Mockery::on(function ($data) {
            return $data['amount'] === 100.00;
        }))
        ->andReturn(new PaymentResponse([
            'status' => 'success',
            'transaction_id' => 'txn_test_123',
        ]));
    
    $response = Payment::gateway('stripe')->pay([
        'amount' => 100.00,
        'currency' => 'USD',
    ]);
    
    $this->assertTrue($response->isSuccessful());
}
```

---

### Test Database Not Reset

**Problem:** Tests interfere with each other.

**Solution:**
```php
class PaymentTest extends TestCase
{
    use RefreshDatabase;  // Reset DB between tests
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock external calls
        Http::fake([...]);
    }
}
```

---

## Getting Help

### Enable Debug Logging

```php
// config/logging.php
'channels' => [
    'payment' => [
        'driver' => 'daily',
        'path' => storage_path('logs/payment.log'),
        'level' => 'debug',
    ],
],

// config/payment.php
'logging' => [
    'enabled' => true,
    'channel' => 'payment',
],
```

### Check Error Codes

```php
$response = Payment::gateway('stripe')->pay($data);

if (!$response->isSuccessful()) {
    $code = $response->getErrorCode();
    $message = $response->getErrorMessage();
    
    Log::error('Payment error', [
        'code' => $code,
        'message' => $message,
        'gateway' => 'stripe',
    ]);
}
```

### Contact Support

If issues persist:
1. Check package GitHub issues
2. Enable debug logging
3. Provide error logs and code snippets
4. Include gateway, Laravel, PHP versions

---

## Quick Reference

| Issue | Quick Fix |
|-------|-----------|
| Config not found | `php artisan vendor:publish` |
| Payment fails | Check API keys are correct |
| Webhook not working | Verify URL is public + HTTPS |
| Test mode not working | Use test/sandbox credentials |
| Currency not supported | Use regional gateway |
| 3DS required | Enable `three_d_secure` option |
