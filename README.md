# WwwPay - Laravel All-In-One Payment Gateway

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.1+-blue.svg" alt="PHP">
  <img src="https://img.shields.io/badge/Laravel-9+-red.svg" alt="Laravel">
  <img src="https://img.shields.io/badge/Package-wwwpay-green.svg" alt="Package">
  <img src="https://img.shields.io/badge/Gateways-33+-orange.svg" alt="Gateways">
  <img src="https://img.shields.io/badge/Tests-89%20passing-brightgreen.svg" alt="Tests">
</p>

> All-in-one payment gateway package for Laravel with 33+ global, crypto, and regional payment methods in a single unified API.

## Features

- **33+ Payment Gateways** - Global, Regional, and Cryptocurrency
- **Unified API** - Consistent interface across all gateways
- **Multi-currency Support** - Automatic currency conversion
- **B2B & P2P Payments** - Business and peer-to-peer transactions
- **Webhook Handling** - Secure webhook processing
- **Subscription Management** - Recurring billing support
- **Security Features** - Fraud detection, PCI compliance
- **Interactive CLI** - Easy installation and configuration

## Supported Gateways

### Global
- Stripe, PayPal

### South Asia
- Bangladesh: bKash, Nagad
- India: UPI, PhonePe, Paytm
- Pakistan: JazzCash, Easypaisa

### Middle East
- PayTabs, Telr, Mada (Saudi Arabia)

### Africa
- Flutterwave, Paystack, M-Pesa, Fawry

### Europe
- Klarna, Adyen, iDEAL, Bancontact, SEPA

### Asia Pacific
- Alipay, WeChat Pay, PayPay, Line Pay, GrabPay

### Americas
- North America: Square, Authorize.net, Moneris
- Latin America: MercadoPago, PagSeguro

### Cryptocurrency
- Bitcoin, Ethereum

## Installation

```bash
composer require shamimstack/wwwpay
```

## Quick Start

### 1. Interactive Installation

```bash
php artisan payment:install
```

This wizard will help you:
- Select payment gateways by region
- Configure environment variables
- Publish configuration files
- Setup database migrations

### 2. Manual Configuration

```bash
# Publish config
php artisan vendor:publish --provider="ShamimStack\WwwPay\Providers\PaymentServiceProvider"

# Run migrations
php artisan migrate
```

### 3. Configure .env

```env
# Default Gateway
PAYMENT_DEFAULT_GATEWAY=stripe

# Stripe
STRIPE_KEY=your_key
STRIPE_SECRET=your_secret
STRIPE_WEBHOOK_SECRET=your_webhook_secret

# PayPal
PAYPAL_CLIENT_ID=your_client_id
PAYPAL_CLIENT_SECRET=your_client_secret
```

## Usage

### Basic Payment

```php
use ShamimStack\WwwPay\Facades\Payment;

// With default gateway
$response = Payment::pay([
    'amount' => 100.00,
    'currency' => 'USD',
    'return_url' => url('/payment/success'),
]);

// With specific gateway
$response = Payment::gateway('paystack')->pay([
    'amount' => 10000.00,
    'currency' => 'NGN',
    'email' => 'customer@example.com',
]);

if ($response->isSuccessful()) {
    $transactionId = $response->getTransactionId();
}
```

### Refunds

```php
// Full refund
$response = Payment::gateway('stripe')->refund($transactionId);

// Partial refund
$response = Payment::gateway('stripe')->refund($transactionId, 50.00);
```

### Subscriptions

```php
$subscription = Payment::gateway('stripe')->subscribe([
    'plan_id' => 'gold_plan',
    'customer_id' => 'customer_123',
]);
```

### B2B Payments

```php
use ShamimStack\WwwPay\B2B\B2BPayment;

// Create invoice
$invoice = B2BPayment::createInvoice([
    'amount' => 1000.00,
    'currency' => 'USD',
    'client_name' => 'Acme Corp',
    'client_email' => 'billing@acme.com',
]);

// Wire transfer
$transfer = B2BPayment::wireTransfer([
    'amount' => 5000.00,
    'currency' => 'EUR',
    'iban' => 'DE89370400440532013000',
    'bic' => 'COBADEFFXXX',
]);
```

### P2P Payments

```php
use ShamimStack\WwwPay\P2P\P2PPayment;

// Send money
$transfer = P2PPayment::sendMoney([
    'amount' => 100.00,
    'currency' => 'USD',
    'recipient_id' => 'user_456',
]);

// Split payment
$split = P2PPayment::splitPayment([
    'total_amount' => 300.00,
    'participants' => ['user_1', 'user_2', 'user_3'],
]);

// Escrow
$escrow = P2PPayment::escrow([
    'amount' => 500.00,
    'currency' => 'USD',
    'released_to' => 'user_seller',
]);
```

## CLI Commands

### payment:install
Interactive installer for setting up gateways.

```bash
php artisan payment:install
php artisan payment:install --gateway=stripe
php artisan payment:install --region=south_asia
```

### payment:gateway:list
List all available gateways.

```bash
php artisan payment:gateway:list
php artisan payment:gateway:list --region=africa
php artisan payment:gateway:list --json
```

### payment:gateway:test
Test a specific gateway connection.

```bash
php artisan payment:gateway:test stripe
php artisan payment:gateway:test paystack --amount=1000 --currency=NGN
```

### payment:webhook:setup
Setup webhook routes for gateways.

```bash
php artisan payment:webhook:setup
php artisan payment:webhook:setup --gateway=stripe
php artisan payment:webhook:setup --all
```

### payment:demo
Generate demo pages.

```bash
php artisan payment:demo
php artisan payment:demo store
php artisan payment:demo subscription
php artisan payment:demo p2p
php artisan payment:demo b2b
```

## Blade Components

The package includes reusable Blade components:

```blade
{{-- Payment Checkout Form --}}
<x-payment::checkout-form :amount="100" :currency="'USD'" :gateways="$gateways" />

{{-- Gateway Selector --}}
<x-payment::gateway-selector :gateways="$gateways" />

{{-- Credit Card Form --}}
<x-payment::card-form />

{{-- Status Badge --}}
<x-payment::status-badge :status="$transaction->status" />

{{-- Transaction Table --}}
<x-payment::transaction-table :transactions="$transactions" />
```

## Database Schema

The package uses two main tables:

### payment_transactions
| Column | Type | Description |
|--------|------|-------------|
| id | uuid | Primary key |
| gateway | string | Payment gateway name |
| type | string | payment/refund |
| amount | decimal | Transaction amount |
| currency | string | Currency code |
| status | string | pending/success/failed |
| transaction_id | string | External transaction ID |
| metadata | json | Additional data |

### payment_subscriptions
| Column | Type | Description |
|--------|------|-------------|
| id | uuid | Primary key |
| gateway | string | Payment gateway |
| plan_id | string | Subscription plan ID |
| customer_id | string | Customer identifier |
| status | string | active/cancelled/expired |
| started_at | timestamp | Start date |
| ends_at | timestamp | End date |

## Webhooks

Setup webhook routes in `routes/web.php`:

```php
use ShamimStack\WwwPay\Http\Controllers\WebhookController;

Route::post('/webhook/{gateway}', [WebhookController::class, 'handle'])
    ->name('payment.webhook');
```

## Requirements

- PHP 8.1+
- Laravel 9.0+
- Extensions: bcmath, json

## Testing

```bash
vendor/bin/phpunit
```

## Security

- Store API keys securely in `.env`
- Validate all input data
- Use HTTPS in production
- Follow PCI DSS guidelines
- Regularly update the package

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

MIT License - see [LICENSE](LICENSE) for details.

## Author

**shamimstack**
