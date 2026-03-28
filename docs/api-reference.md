# WwwPay - API Reference

Complete API documentation for WwwPay payment gateway package.

## Table of Contents

1. [Payment Facade](#payment-facade)
2. [PaymentManager](#paymentmanager)
3. [PaymentGateway Interface](#paymentgateway-interface)
4. [PaymentResponse](#paymentresponse)
5. [B2BPayment](#b2bpayment)
6. [P2PPayment](#p2ppayment)
7. [CurrencyConverter](#currencyconverter)
8. [FraudDetection](#frauddetection)
9. [PciCompliance](#pcicompliance)
10. [Models](#models)

---

## Payment Facade

Main entry point for all payment operations.

### `Payment::pay(array $data)`

Process a payment.

```php
$response = Payment::pay([
    'amount' => 100.00,           // Required: Amount
    'currency' => 'USD',          // Required: Currency code (ISO 4217)
    'return_url' => '...',        // Required: Redirect URL on success
    'cancel_url' => '...',        // Required: Redirect URL on cancel
    'email' => 'customer@...',    // Optional: Customer email
    'name' => 'John Doe',         // Optional: Customer name
    'metadata' => [],              // Optional: Custom metadata
]);
```

**Returns:** `PaymentResponse`

---

### `Payment::gateway(string $name)`

Get a specific gateway instance.

```php
$gateway = Payment::gateway('paystack');
$response = $gateway->pay([...]);
```

**Parameters:**
- `$name` - Gateway name (stripe, paypal, paystack, etc.)

**Returns:** `PaymentGateway`

---

### `Payment::refund(string $transactionId, ?float $amount = null, array $options = [])`

Process a refund.

```php
// Full refund
$response = Payment::refund('txn_123');

// Partial refund
$response = Payment::refund('txn_123', 50.00);

// With reason
$response = Payment::refund('txn_123', null, [
    'reason' => 'Customer request',
]);
```

**Parameters:**
- `$transactionId` - Original transaction ID
- `$amount` - Amount to refund (null for full refund)
- `$options` - Additional options (reason, metadata)

**Returns:** `PaymentResponse`

---

### `Payment::subscribe(array $data)`

Create a subscription.

```php
$subscription = Payment::subscribe([
    'plan_id' => 'price_123',
    'customer_email' => 'customer@example.com',
    'payment_method' => 'pm_card_visa',
]);
```

**Returns:** `Subscription`

---

### `Payment::verify(Request $request)`

Verify a payment from webhook/callback.

```php
$response = Payment::verify($request);
```

**Returns:** `PaymentResponse`

---

## PaymentManager

### `->gateway(string $name)`

Get a gateway instance.

```php
$manager = app('payment');
$gateway = $manager->gateway('stripe');
```

---

### `->setDefaultGateway(string $name)`

Set the default gateway.

```php
$manager->setDefaultGateway('paypal');
```

---

### `->getDefaultGateway()`

Get the default gateway name.

```php
$name = $manager->getDefaultGateway(); // 'stripe'
```

---

## PaymentGateway Interface

All gateways implement this interface.

### `->pay(array $data)`

Process a payment.

```php
$response = $gateway->pay([
    'amount' => 100.00,
    'currency' => 'USD',
    // ... gateway-specific parameters
]);
```

---

### `->refund(string $transactionId, ?float $amount = null)`

Refund a transaction.

```php
$response = $gateway->refund('txn_123', 50.00);
```

---

### `->subscribe(array $data)`

Create subscription.

```php
$subscription = $gateway->subscribe([
    'plan_id' => 'plan_123',
    'customer_email' => 'customer@example.com',
]);
```

---

### `->cancelSubscription(string $subscriptionId)`

Cancel a subscription.

```php
$gateway->cancelSubscription('sub_123');
```

---

### `->handleWebhook(Request $request, bool $verify = true)`

Handle webhook notification.

```php
$verified = $gateway->handleWebhook($request, true);
```

---

### `->verify(array $data)`

Verify payment status.

```php
$response = $gateway->verify(['transaction_id' => 'txn_123']);
```

---

### `->getName()`

Get gateway name.

```php
$name = $gateway->getName(); // 'stripe'
```

---

### `->getSupportedCurrencies()`

Get supported currencies.

```php
$currencies = $gateway->getSupportedCurrencies();
// ['USD', 'EUR', 'GBP', ...]
```

---

## PaymentResponse

Returned by all payment operations.

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `status` | string | success, failed, pending, cancelled |
| `transaction_id` | string | External transaction ID |
| `amount` | float | Transaction amount |
| `currency` | string | Currency code |
| `message` | string | Response message |
| `data` | array | Raw gateway response |
| `redirect_url` | string | Redirect URL (if applicable) |

### Methods

#### `->isSuccessful()`

Check if payment was successful.

```php
if ($response->isSuccessful()) {
    // Payment completed
}
```

---

#### `->isFailed()`

Check if payment failed.

```php
if ($response->isFailed()) {
    // Handle failure
}
```

---

#### `->isPending()`

Check if payment is pending.

```php
if ($response->isPending()) {
    // Await confirmation
}
```

---

#### `->isRedirect()`

Check if redirect is needed.

```php
if ($response->isRedirect()) {
    return redirect($response->getRedirectUrl());
}
```

---

#### `->getTransactionId()`

Get transaction ID.

```php
$txnId = $response->getTransactionId();
```

---

#### `->getErrorMessage()`

Get error message.

```php
$error = $response->getErrorMessage();
```

---

#### `->getErrorCode()`

Get error code.

```php
$code = $response->getErrorCode();
```

---

#### `->getRedirectUrl()`

Get redirect URL.

```php
$url = $response->getRedirectUrl();
```

---

#### `->getMetadata()`

Get metadata.

```php
$meta = $response->getMetadata();
```

---

## B2BPayment

Business-to-business payment operations.

### `B2BPayment::createInvoice(array $data)`

Create an invoice.

```php
$invoice = B2BPayment::createInvoice([
    'amount' => 1000.00,
    'currency' => 'USD',
    'client_name' => 'Acme Corp',
    'client_email' => 'billing@acme.com',
    'due_date' => '2024-12-31',
    'items' => [
        [
            'description' => 'Web Development',
            'quantity' => 1,
            'price' => 1000.00,
        ],
    ],
    'notes' => 'Payment due within 30 days',
]);
```

**Returns:** `B2BInvoice`

---

### `B2BPayment::sendInvoice(string $invoiceId)`

Send invoice to client.

```php
B2BPayment::sendInvoice('inv_123');
```

---

### `B2BPayment::getInvoice(string $invoiceId)`

Get invoice details.

```php
$invoice = B2BPayment::getInvoice('inv_123');
```

---

### `B2BPayment::wireTransfer(array $data)`

Initiate wire transfer.

```php
$transfer = B2BPayment::wireTransfer([
    'amount' => 5000.00,
    'currency' => 'EUR',
    'iban' => 'DE89370400440532013000',
    'bic' => 'COBADEFFXXX',
    'beneficiary_name' => 'Acme GmbH',
    'beneficiary_address' => 'Berlin, Germany',
    'reference' => 'Invoice #1234',
]);
```

---

### `B2BPayment::createPurchaseOrder(array $data)`

Create purchase order.

```php
$order = B2BPayment::createPurchaseOrder([
    'vendor_id' => 'vendor_123',
    'items' => [...],
    'delivery_date' => '2024-12-15',
]);
```

---

## P2PPayment

Peer-to-peer payment operations.

### `P2PPayment::sendMoney(array $data)`

Send money to another user.

```php
$transfer = P2PPayment::sendMoney([
    'amount' => 100.00,
    'currency' => 'USD',
    'recipient_id' => 'user_456',
    'note' => 'For dinner',
]);
```

---

### `P2PPayment::requestMoney(array $data)`

Request money from another user.

```php
$request = P2PPayment::requestMoney([
    'amount' => 50.00,
    'currency' => 'USD',
    'from_user' => 'user_123',
    'message' => 'Split for lunch',
]);
```

---

### `P2PPayment::splitPayment(array $data)`

Split a payment among multiple users.

```php
$split = P2PPayment::splitPayment([
    'total_amount' => 300.00,
    'currency' => 'USD',
    'participants' => [
        ['user_id' => 'user_1', 'amount' => 100.00],
        ['user_id' => 'user_2', 'amount' => 100.00],
        ['user_id' => 'user_3', 'amount' => 100.00],
    ],
]);
```

---

### `P2PPayment::escrow(array $data)`

Create escrow payment.

```php
$escrow = P2PPayment::escrow([
    'amount' => 500.00,
    'currency' => 'USD',
    'released_to' => 'seller_user',
    'released_on' => '2024-12-31',
    'condition' => 'On successful delivery',
]);
```

---

### `P2PPayment::releaseEscrow(string $escrowId)`

Release escrow funds.

```php
P2PPayment::releaseEscrow('escrow_123');
```

---

## CurrencyConverter

### Constructor

```php
$converter = new CurrencyConverter('USD'); // Base currency
```

---

### `->convert(float $amount, string $from, string $to)`

Convert amount between currencies.

```php
$eur = $converter->convert(100.00, 'USD', 'EUR');
// Returns: 92.50 (approximate)
```

---

### `->getRate(string $from, string $to)`

Get exchange rate.

```php
$rate = $converter->getRate('USD', 'EUR');
// Returns: 0.925
```

---

### `->format(float $amount, string $currency)`

Format amount for display.

```php
$formatted = $converter->format(100.00, 'EUR');
// Returns: '€100.00'
```

---

### `->getSupportedCurrencies()`

Get list of supported currencies.

```php
$currencies = $converter->getSupportedCurrencies();
```

---

## FraudDetection

### `->analyze(array $data)`

Analyze transaction for fraud.

```php
$result = $fraud->analyze([
    'amount' => 1000.00,
    'card_country' => 'US',
    'ip_country' => 'US',
    'user_id' => $user->id,
    'email' => $user->email,
    'user_history' => $user->payment_history,
]);
```

**Returns:** `FraudAnalysisResult`

---

### `->isHighRisk()`

Check if transaction is high risk.

```php
if ($result->isHighRisk()) {
    // Block transaction
}
```

---

### `->getRiskScore()`

Get risk score (0-100).

```php
$score = $result->getRiskScore();
if ($score > 70) {
    // Require verification
}
```

---

### `->getRiskFactors()`

Get detected risk factors.

```php
$factors = $result->getRiskFactors();
// ['high_amount', 'new_device', 'different_country']
```

---

## PciCompliance

### `->validateCardNumber(string $number)`

Validate card number (Luhn algorithm).

```php
if (!$pci->validateCardNumber('4242424242424242')) {
    // Invalid card
}
```

---

### `->detectCardType(string $number)`

Detect card type.

```php
$type = $pci->detectCardType('4242424242424242');
// Returns: 'visa', 'mastercard', 'amex', 'discover', etc.
```

---

### `->validateExpiry(string $month, string $year)`

Validate card expiry.

```php
if (!$pci->validateExpiry('12', '2025')) {
    // Card expired
}
```

---

### `->validateCvv(string $cvv, string $cardType)`

Validate CVV.

```php
if (!$pci->validateCvv('123', 'visa')) {
    // Invalid CVV
}
```

---

### `->maskCardNumber(string $number)`

Mask card number for storage.

```php
$masked = $pci->maskCardNumber('4242424242424242');
// Returns: '************4242'
```

---

### `->hashCardNumber(string $number)`

Hash card number for comparison.

```php
$hash = $pci->hashCardNumber('4242424242424242');
```

---

## Models

### Transaction Model

```php
use ShamimStack\WwwPay\Models\Transaction;

// Get transaction
$transaction = Transaction::find($id);

// Find by external ID
$transaction = Transaction::where('transaction_id', 'txn_123')->first();

// Get by status
$failed = Transaction::where('status', 'failed')->get();

// Get recent
$recent = Transaction::where('user_id', $user->id)
    ->orderBy('created_at', 'desc')
    ->take(10)
    ->get();
```

#### Transaction Properties

| Property | Type | Description |
|----------|------|-------------|
| `id` | UUID | Primary key |
| `transaction_id` | string | External transaction ID |
| `gateway` | string | Payment gateway used |
| `type` | string | payment, refund |
| `amount` | decimal | Transaction amount |
| `currency` | string | Currency code |
| `status` | string | pending, success, failed |
| `user_id` | integer | Associated user |
| `metadata` | JSON | Additional data |

---

### Subscription Model

```php
use ShamimStack\WwwPay\Models\Subscription;

// Get subscription
$subscription = Subscription::find($id);

// Find by gateway ID
$subscription = Subscription::where('gateway_id', 'sub_123')->first();

// Get active subscriptions
$active = Subscription::where('status', 'active')->get();

// Get by user
$userSubscriptions = Subscription::where('user_id', $user->id)->get();
```

#### Subscription Properties

| Property | Type | Description |
|----------|------|-------------|
| `id` | UUID | Primary key |
| `gateway_id` | string | External subscription ID |
| `gateway` | string | Payment gateway |
| `plan_id` | string | Subscription plan |
| `user_id` | integer | Associated user |
| `status` | string | active, cancelled, expired |
| `started_at` | datetime | Start date |
| `ends_at` | datetime | End date (nullable) |

---

## Events

### `PaymentSuccessful`

Fired when a payment succeeds.

```php
Event::listen(PaymentSuccessful::class, function ($event) {
    $transaction = $event->transaction;
    // Handle success
});
```

---

### `PaymentFailed`

Fired when a payment fails.

```php
Event::listen(PaymentFailed::class, function ($event) {
    $transaction = $event->transaction;
    $error = $event->error;
    // Handle failure
});
```

---

### `RefundProcessed`

Fired when a refund is processed.

```php
Event::listen(RefundProcessed::class, function ($event) {
    $refund = $event->refund;
    // Handle refund
});
```

---

## Exceptions

### `PaymentException`

Base exception for payment errors.

```php
try {
    Payment::gateway('stripe')->pay($data);
} catch (PaymentException $e) {
    $e->getMessage();  // Error message
    $e->getCode();    // Error code
    $e->getGateway(); // Gateway name
}
```

---

### `InvalidConfigurationException`

Thrown for configuration errors.

```php
try {
    Payment::gateway('invalid_gateway');
} catch (InvalidConfigurationException $e) {
    // Gateway not configured
}
```
