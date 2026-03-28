# WwwPay - All-in-One Payment Gateway for Laravel

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.1+-blue.svg" alt="PHP">
  <img src="https://img.shields.io/badge/Laravel-9+-red.svg" alt="Laravel">
  <img src="https://img.shields.io/badge/Gateways-33+-orange.svg" alt="Gateways">
  <img src="https://img.shields.io/badge/Tests-89%20passing-brightgreen.svg" alt="Tests">
  <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License">
</p>

<p align="center">
  <a href="https://github.com/shamimlaravel/wwwpay/actions"><img src="https://github.com/shamimlaravel/wwwpay/workflows/Tests/badge.svg" alt="Build Status"></a>
  <a href="https://packagist.org/packages/shamimstack/wwwpay"><img src="https://img.shields.io/packagist/v/shamimstack/wwwpay.svg" alt="Packagist"></a>
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
- **REST API** - Ready-to-use API controllers
- **Events System** - Laravel event-driven architecture
- **Middleware** - Security and webhook verification
- **Traits** - HasPayments, HasSubscriptions, HandlesCurrency

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

### 2. Configure .env

```env
PAYMENT_DEFAULT_GATEWAY=stripe
STRIPE_KEY=your_key
STRIPE_SECRET=your_secret
```

### 3. Make Payment

```php
use ShamimStack\WwwPay\Facades\Payment;

$response = Payment::pay([
    'amount' => 100.00,
    'currency' => 'USD',
    'return_url' => url('/payment/success'),
]);

if ($response->isRedirect()) {
    return redirect($response->getRedirectUrl());
}
```

## Usage Examples

### Payments
```php
// Default gateway
Payment::pay(['amount' => 100, 'currency' => 'USD']);

// Specific gateway
Payment::gateway('paystack')->pay([
    'amount' => 50000,
    'currency' => 'NGN',
    'email' => 'customer@example.com',
]);
```

### Subscriptions
```php
$subscription = Payment::gateway('stripe')->subscribe([
    'plan_id' => 'price_monthly',
    'customer_email' => 'user@example.com',
]);
```

### B2B Payments
```php
use ShamimStack\WwwPay\B2B\B2BPayment;

$invoice = B2BPayment::createInvoice([
    'amount' => 1000.00,
    'currency' => 'USD',
    'client_name' => 'Acme Corp',
    'client_email' => 'billing@acme.com',
]);
```

### P2P Payments
```php
use ShamimStack\WwwPay\P2P\P2PPayment;

$transfer = P2PPayment::sendMoney([
    'amount' => 100.00,
    'currency' => 'USD',
    'recipient_id' => 'user_456',
]);
```

## CLI Commands

| Command | Description |
|---------|-------------|
| `payment:install` | Interactive setup wizard |
| `payment:list` | List all gateways |
| `payment:test stripe` | Test gateway connection |
| `payment:webhook` | Setup webhooks |
| `payment:demo` | Generate demo pages |

## Blade Components

```blade
<x-payment::checkout-form :amount="100" :currency="'USD'" />
<x-payment::card-form />
<x-payment::status-badge :status="$transaction->status" />
```

## Traits

```php
use ShamimStack\WwwPay\Traits\HasPayments;
use ShamimStack\WwwPay\Traits\HasSubscriptions;

class User extends Model
{
    use HasPayments, HasSubscriptions;
}

// Now you can:
$user->processPayment(['amount' => 100, 'currency' => 'USD']);
$user->subscribe('price_monthly');
$user->totalSpent();
```

## Middleware

```php
// routes/web.php
Route::post('/webhook/{gateway}', [WebhookController::class, 'handle'])
    ->middleware('verify.payment.security');
```

## API Routes

```php
// In your routes/api.php
require 'vendor/shamimstack/wwwpay/routes/payment-api.php';
```

## Documentation

- [Installation Guide](docs/implementation.md)
- [API Reference](docs/api-reference.md)
- [Quick Start](docs/quickstart.md)
- [Gateway Docs](docs/gateways/)

## Testing

```bash
./vendor/bin/phpunit
```

## Requirements

- PHP 8.1+
- Laravel 9.0+
- Extensions: bcmath, json

## Contributing

Contributions are welcome! Please submit a Pull Request.

## License

MIT License - see [LICENSE](LICENSE) for details.

## Author

**Shamim Hassan** - [shamimlaravel](https://github.com/shamimlaravel)
