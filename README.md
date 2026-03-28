# WwwPay - All-in-One Payment Gateway for Laravel

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.1+-blue.svg" alt="PHP">
  <img src="https://img.shields.io/badge/Laravel-9+-red.svg" alt="Laravel">
  <img src="https://img.shields.io/badge/Gateways-165-orange.svg" alt="Gateways">
  <img src="https://img.shields.io/badge/Tests-153%20passing-brightgreen.svg" alt="Tests">
  <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License">
</p>

<p align="center">
  <a href="https://github.com/shamimlaravel/wwwpay/actions"><img src="https://github.com/shamimlaravel/wwwpay/workflows/Tests/badge.svg" alt="Build Status"></a>
  <a href="https://packagist.org/packages/shamimstack/wwwpay"><img src="https://img.shields.io/packagist/v/shamimstack/wwwpay.svg" alt="Packagist"></a>
  <img src="https://img.shields.io/github/v/tag/shamimlaravel/wwwpay?label=version" alt="Version">
</p>

> All-in-one payment gateway package for Laravel with **165 global, crypto, regional payment methods**, and **Binance P2P & B2B** integration in a single unified API.

## Features

- **165 Payment Gateways** - Global, Regional, Cryptocurrency, and Binance P2P/B2B
- **Unified API** - Consistent interface across all gateways
- **Multi-currency Support** - Automatic currency conversion
- **B2B Payments** - Invoices, wire transfers, ACH, corporate cards
- **P2P Payments** - Send money, split bills, escrow, international transfers
- **Binance P2P** - Peer-to-peer crypto trading with ads, orders, appeals
- **Binance B2B** - Business payments, merchant accounts, bulk payments, settlements
- **Webhook Handling** - Secure webhook processing with signature verification
- **Subscription Management** - Recurring billing support
- **Security Features** - Fraud detection, PCI compliance helpers
- **Interactive CLI** - Easy installation and configuration
- **REST API** - Ready-to-use API controllers (6 controllers, 50+ endpoints)
- **Events System** - Laravel event-driven architecture (7 events)
- **Middleware** - Security and webhook verification (2 middleware)
- **Traits** - HasPayments, HasSubscriptions, HandlesCurrency, HasGatewayTest

## Supported Gateways

### Global Digital Wallets & BNPL
| Gateway | Region | Features |
|---------|---------|----------|
| Stripe | Worldwide | Cards, Subscriptions, Connect |
| PayPal | Worldwide | Checkout, Subscriptions, Payouts |
| Apple Pay | Worldwide | Mobile Payment |
| Google Pay | Worldwide | Mobile Payment |
| Samsung Pay | Worldwide | Mobile Payment |
| Afterpay | Australia/UK/US | Buy Now Pay Later |
| Affirm | US | Buy Now Pay Later |
| Wise | Worldwide | International Transfers |
| Payoneer | Worldwide | Cross-border Payments |

### Bangladesh
| Gateway | Type |
|---------|------|
| bKash | Mobile Money |
| Nagad | Mobile Banking |
| Rocket | Mobile Banking |
| Upay | Mobile Wallet |
| ShurjoPay | Payment Gateway |
| SSLCommerz | Payment Gateway |
| AamarPay | Payment Gateway |
| Pathao | Delivery & Payment |
| CashBaba | Payment Gateway |
| QPay | Payment Gateway |
| FastPay | Payment Gateway |
| BangoPay | Payment Gateway |
| FlexPay | Payment Gateway |
| OnePay | Payment Gateway |

### Southeast Asia - Indonesia
| Gateway | Type |
|---------|------|
| GoPay | Digital Wallet |
| OVO | Digital Wallet |
| DANA | Digital Wallet |
| LinkAja | E-wallet |
| Midtrans | Payment Gateway |
| Xendit | Payment Gateway |

### Southeast Asia - Vietnam
| Gateway | Type |
|---------|------|
| MoMo | Mobile Wallet |
| ZaloPay | Digital Wallet |
| VNPay | Payment Gateway |
| ViettelPay | Mobile Payment |

### Southeast Asia - Thailand
| Gateway | Type |
|---------|------|
| TrueMoney | Mobile Wallet |
| 2C2P | Payment Gateway |
| Omise | Payment Gateway |

### Southeast Asia - Philippines
| Gateway | Type |
|---------|------|
| GCash | Mobile Wallet |
| Maya | Digital Wallet |
| Dragonpay | Payment Gateway |

### Southeast Asia - Malaysia
| Gateway | Type |
|---------|------|
| Touch 'n Go | E-wallet |
| Boost | Digital Wallet |
| iPay88 | Payment Gateway |

### Southeast Asia - Singapore
| Gateway | Type |
|---------|------|
| PayNow | Real-time Payments |
| NETS | Payment Network |

### Southeast Asia - Myanmar
| Gateway | Type |
|---------|------|
| WavePay | Mobile Money |

### Southeast Asia - Cambodia
| Gateway | Type |
|---------|------|
| Wing | Mobile Money |

### India
| Gateway | Type |
|---------|------|
| UPI | Real-time Payments |
| PhonePe | Digital Wallet |
| Paytm | Digital Wallet |
| Amazon Pay | E-wallet |
| FreeCharge | Digital Payments |
| Mobikwik | Mobile Payments |

### Pakistan
| Gateway | Type |
|---------|------|
| JazzCash | Mobile Wallet |
| Easypaisa | Mobile Banking |
| SimPay | Digital Payments |

### South Asia
| Gateway | Country | Type |
|---------|---------|------|
| AirtelMoney | Multiple | Mobile Money |

### Middle East & MENA
| Gateway | Country | Type |
|---------|---------|------|
| PayTabs | Regional | Online Payments |
| Telr | UAE | Payment Gateway |
| Mada | Saudi Arabia | Debit Cards |
| ArabBankPay | Jordan | Online Payments |
| Checkout | UAE | Payment Gateway |
| HyperPay | Saudi Arabia | Payment Gateway |
| Fawry | Egypt | E-payment Platform |
| Tabby | UAE | Buy Now Pay Later |
| Tamara | Saudi Arabia | BNPL |
| STC Pay | Saudi Arabia | Digital Payments |

### Latin America - Brazil
| Gateway | Type |
|---------|------|
| PIX | Instant Payment |
| Boleto | Bank Slip |
| PicPay | Digital Wallet |

### Latin America - Mexico
| Gateway | Type |
|---------|------|
| OXXO | Cash Voucher |
| SPEI | Bank Transfer |
| Conekta | Payment Gateway |

### Latin America - Other
| Gateway | Country | Type |
|---------|---------|------|
| MercadoPago | Argentina | Payment Gateway |
| PSE | Colombia | Bank Transfer |
| WebPay | Chile | Payment Gateway |
| Culqi | Peru | Payment Gateway |

### Africa
| Gateway | Country | Type |
|---------|---------|------|
| Flutterwave | Pan-Africa | Payment Gateway |
| Paystack | Nigeria | Payment Gateway |
| PayFast | South Africa | Payment Gateway |
| SnapScan | South Africa | QR Payments |
| M-Pesa | Kenya | Mobile Money |
| Interswitch | Nigeria | Payment Gateway |
| Paga | Nigeria | Mobile Money |
| VoguePay | Nigeria | Payment Gateway |
| OrangeMoney | Africa | Mobile Money |
| MtnMobileMoney | Africa | Mobile Money |
| AirtelAfrica | Africa | Mobile Money |
| Masary | Egypt | E-payment |

### Europe
| Gateway | Country | Type |
|---------|---------|------|
| Klarna | Sweden | Buy Now Pay Later |
| Adyen | Netherlands | Payment Platform |
| iDEAL | Netherlands | Online Banking |
| Bancontact | Belgium | Payment Method |
| SEPA | EU | Bank Transfer |
| Giropay | Germany | Online Banking |
| Sofort | Germany | Instant Transfer |
| Przelewy24 | Poland | Online Banking |
| Trustly | Sweden | Direct Banking |
| Multibanco | Portugal | Multi-channel |
| EPS | Austria | Payment Standard |
| Payguard | Europe | Payment Gateway |

### Asia Pacific
| Gateway | Country | Type |
|---------|---------|------|
| Alipay | China | Digital Wallet |
| WeChat Pay | China | Digital Payments |
| PayPay | Japan | Mobile Payments |
| Line Pay | Japan | Mobile Wallet |
| GrabPay | Southeast Asia | E-wallet |
| KakaoPay | South Korea | Digital Payments |
| NaverPay | South Korea | Online Payments |
| Toss | South Korea | Financial Platform |
| dPay | South Korea | Digital Payments |
| RakutenPay | Japan | E-commerce Payments |
| Merpay | Japan | Mobile Payments |
| 7-Eleven | Thailand | Cash Payment |

### Americas
| Gateway | Region | Type |
|---------|---------|------|
| Square | North America | Payment Platform |
| Authorize.net | North America | Payment Gateway |
| Moneris | Canada | Payment Processor |
| MercadoPago | Latin America | Payment Platform |
| PagSeguro | Brazil | Payment Gateway |
| BlueSnap | Americas | Payment Gateway |
| Chargify | Americas | Subscription Billing |
| PayU | Latin America | Payment Gateway |
| EBANX | Brazil | Payment Processor |
| dLocal | Emerging Markets | Payment Platform |

### Cryptocurrency
| Gateway | Type |
|---------|------|
| Bitcoin | Crypto |
| Ethereum | Crypto |
| USDT | Stablecoin |
| USDC | Stablecoin |
| Litecoin | Crypto |
| Ripple (XRP) | Crypto |
| **Binance** | P2P & B2B |

### Binance P2P Features
- Create/Update/Delete P2P advertisements
- Place and manage P2P orders
- Payment confirmation flow
- Crypto release mechanism
- Dispute appeals
- User/merchant profiles
- Trade history
- Real-time rates

### Binance B2B Features
- Merchant account management
- Payment links with QR codes
- Bulk payment processing
- Settlement management
- Withdrawal requests
- Transaction history
- Wallet balance
- Webhook configuration

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

// Binance P2P
Payment::gateway('binance')->pay([
    'amount' => 100,
    'currency' => 'USDT',
    'fiat' => 'USD',
    'side' => 'BUY',
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

// Wire transfer
$response = B2BPayment::wireTransfer([
    'amount' => 5000.00,
    'iban' => 'DE89370400440532013000',
]);

// Bulk payments
$batch = B2BPayment::bulkPayment([
    ['recipient_id' => 'user1', 'amount' => 100],
    ['recipient_id' => 'user2', 'amount' => 200],
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
    'total_amount' => 300,
    'participants' => [
        ['user_id' => 'user1', 'amount' => 100],
        ['user_id' => 'user2', 'amount' => 200],
    ],
]);
```

### Binance P2P
```php
$binance = Payment::gateway('binance');
$p2p = $binance->p2p();

// Create ad
$ad = $p2p->createAd([
    'asset' => 'USDT',
    'fiat' => 'USD',
    'price' => 1.00,
    'available_amount' => 1000,
    'payment_methods' => ['BANK'],
]);

// Place order
$response = $p2p->placeOrder([
    'ad_id' => $ad['ad_id'],
    'amount' => 100,
    'side' => 'BUY',
]);

// Confirm payment
$p2p->confirmPayment($orderId);

// Release crypto (seller)
$p2p->releaseCrypto($orderId);
```

### Binance B2B
```php
$binance = Payment::gateway('binance');
$b2b = $binance->b2b();

// Create payment link
$link = $b2b->createPaymentLink([
    'amount' => 100,
    'currency' => 'USDT',
    'description' => 'Order #12345',
]);

// Process bulk payments
$batch = $b2b->processBulkPayment([
    'payments' => [
        ['recipient_id' => 'user1', 'amount' => 100],
        ['recipient_id' => 'user2', 'amount' => 200],
    ],
]);

// Get settlement
$settlements = $b2b->getSettlement();
```

## CLI Commands

| Command | Description |
|---------|-------------|
| `payment:install` | Interactive setup wizard |
| `payment:list` | List all 74 gateways |
| `payment:test stripe` | Test gateway connection |
| `payment:webhook` | Setup webhooks |
| `payment:demo` | Generate demo pages |

## REST API Endpoints

### Payment
- `POST /api/payment/pay` - Process payment
- `POST /api/payment/refund/{id}` - Refund payment
- `GET /api/payment/{id}` - Get payment details

### Subscriptions
- `POST /api/payment/subscriptions` - Create subscription
- `GET /api/payment/subscriptions/{id}` - Get subscription
- `DELETE /api/payment/subscriptions/{id}` - Cancel subscription

### B2B
- `POST /api/payment/b2b/invoice` - Create invoice
- `POST /api/payment/b2b/wire-transfer` - Wire transfer
- `POST /api/payment/b2b/bulk-payment` - Bulk payment

### P2P
- `POST /api/payment/p2p/send` - Send money
- `POST /api/payment/p2p/split` - Split payment
- `POST /api/payment/p2p/escrow` - Create escrow

### Binance (38 endpoints)
- P2P: ads, orders, appeals, rates, trade history
- B2B: merchant, payment links, bulk payments, settlements

### Webhooks
- `POST /api/webhook/{gateway}` - Handle webhook

## Events

| Event | Description |
|-------|-------------|
| PaymentSuccessful | Fired on successful payment |
| PaymentFailed | Fired on failed payment |
| RefundProcessed | Fired after refund |
| SubscriptionCreated | Fired on subscription creation |
| SubscriptionCancelled | Fired on subscription cancellation |
| WebhookReceived | Fired on webhook receipt |

## Middleware

```php
// Verify payment security
Route::post('/payment', ...)
    ->middleware('verify.payment.security');

// Verify webhook signature
Route::post('/webhook/{gateway}', ...)
    ->middleware('verify.webhook.signature');
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

## Testing

```bash
# Run all tests
./vendor/bin/phpunit

# Run with coverage
./vendor/bin/phpunit --coverage-text

# Run specific test
./vendor/bin/phpunit tests/Unit/BinanceTest.php
```

## Requirements

- PHP 8.1+
- Laravel 9.0+
- Extensions: bcmath, json, openssl

## Documentation

- [Installation Guide](docs/implementation.md)
- [API Reference](docs/api-reference.md)
- [Quick Start](docs/quickstart.md)
- [Troubleshooting](docs/troubleshooting.md)
- [Gateway Docs](docs/gateways/)
- [Binance Gateway](docs/gateways/binance.md)

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0.2 | 2026-03-29 | Binance P2P & B2B, 74 gateways, 153 tests |
| 1.0.0 | 2024-03-28 | Initial release, 33 gateways |

## Contributing

Contributions are welcome! Please submit a Pull Request.

## License

MIT License - see [LICENSE](LICENSE) for details.

## Author

**Shamim Hassan** - [shamimlaravel](https://github.com/shamimlaravel)

## Repository

- GitHub: https://github.com/shamimlaravel/wwwpay
- Packagist: https://packagist.org/packages/shamimstack/wwwpay
