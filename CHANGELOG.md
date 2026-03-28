# Changelog

All notable changes to **WwwPay** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.4] - 2026-03-29

### 🚀 Major Release - 195 Payment Gateways

#### New Features
- **30 New Regional Gateways** added
- **Africa**: Pesapal, Jenga, Flutterwave U, Sumsub
- **MENA**: QPay (Qatar), OmanNet, Benefit (Bahrain)
- **Asia Pacific**: HK Pay, EasyPay (Taiwan), NewebPay, CvsPay
- **Europe**: Nordea, Barclay, Moneta, Clearhaus
- **Latin America**: MercadoPago CO, TodoPago (Argentina)
- **Americas**: Stripe CA
- **Global**: ePay, 2Checkout, Paymentwall, Gumroad, Paddle, LemonSqueezy, BitPay, CoinBase
- **South Asia**: Billdesk, Atom, Payzii, SSLCommerz BD

---

## [1.0.3] - 2026-03-29

### 🚀 Major Release - 165 Payment Gateways

#### New Features
- **39 New Regional Gateways** added
- **Indonesia**: Doku, OY, CIMB
- **Vietnam**: OnePay, Vimo
- **Thailand**: Rabbit
- **Africa**: Yoco, PayDunya, TouchPay, Wari, WaveCIMA, SafaricomMpesa
- **MENA**: KNet (Kuwait)
- **Europe**: Paysafecard, Qiwi, YooKassa, Sberbank
- **Americas**: Clover, Adyen Test, EBANX Local
- **Global**: Skrill, Neteller, AstroPay, Rapyd, Payeer, Paxum, SticPay
- **South Asia**: Juspay, Cashfree, Razorpay, Instamojo, PayUmoney
- **Asia Pacific**: PayLah (Singapore), PayPay JP, Stripe JP

#### Previous Gateways Included
- **Bangladesh (14)**: bKash, Nagad, Rocket, Upay, ShurjoPay, SSLCommerz, AamarPay, Pathao, CashBaba, QPay, FastPay, BangoPay, FlexPay, OnePay
- **Indonesia (9)**: GoPay, OVO, DANA, LinkAja, Midtrans, Xendit, Doku, OY, CIMB
- **Vietnam (6)**: MoMo, ZaloPay, VNPay, ViettelPay, OnePay, Vimo
- **Thailand (5)**: TrueMoney, 2C2P, Omise, Rabbit, 7-Eleven
- **Philippines (3)**: GCash, Maya, Dragonpay
- **Malaysia (3)**: Touch'n Go, Boost, iPay88
- **Singapore (3)**: PayNow, NETS, PayLah
- **Myanmar (1)**: WavePay
- **Cambodia (1)**: Wing
- **Brazil (3)**: PIX, Boleto, PicPay
- **Mexico (3)**: OXXO, SPEI, Conekta
- **Latin America (4)**: MercadoPago AR, PSE, WebPay, Culqi
- **MENA (12)**: PayTabs, Telr, Mada, ArabBankPay, Checkout, HyperPay, Fawry, Tabby, Tamara, STC Pay, KNet, PayFort
- **Africa (18)**: Flutterwave, Paystack, PayFast, SnapScan, M-Pesa, SafaricomMpesa, Yoco, PayDunya, TouchPay, Wari, WaveCIMA, Interswitch, Paga, VoguePay, OrangeMoney, MtnMobileMoney, AirtelAfrica, Masary
- **Europe (14)**: Klarna, Adyen, iDEAL, Bancontact, SEPA, Giropay, Sofort, Przelewy24, Trustly, Multibanco, EPS, Payguard, Paysafecard, Qiwi, YooKassa, Sberbank
- **Americas (13)**: Square, Authorize.net, Moneris, MercadoPago, PagSeguro, BlueSnap, Chargify, PayU, EBANX, dLocal, Clover, Adyen Test, EBANX Local
- **Asia Pacific (16)**: Alipay, WeChat Pay, PayPay, Line Pay, GrabPay, KakaoPay, NaverPay, Toss, dPay, RakutenPay, Merpay, PayPay JP, Stripe JP, PayLah
- **South Asia (10)**: UPI, PhonePe, Paytm, AmazonPay, FreeCharge, Mobikwik, AirtelMoney, SimPay, Juspay, Cashfree, Razorpay, Instamojo, PayUmoney
- **Global (12)**: Stripe, PayPal, ApplePay, GooglePay, SamsungPay, Afterpay, Affirm, Wise, Payoneer, Skrill, Neteller, AstroPay, Rapyd, Payeer, Paxum, SticPay
- **Crypto (7)**: Bitcoin, Ethereum, USDT, USDC, Litecoin, Ripple, Binance

---

## [1.0.2] - 2026-03-29

### 🎉 Major Release - Binance P2P & B2B Integration

#### New Features
- **Binance P2P Gateway** - Full peer-to-peer trading support
- **Binance B2B Gateway** - Business payment solutions
- **38 REST API Endpoints** - Complete Binance integration
- **BinanceController** - RESTful API for all operations

#### Binance P2P Features
- Create/Update/Delete P2P advertisements
- Place and manage P2P orders
- Payment confirmation flow
- Crypto release mechanism
- Dispute appeals
- User/merchant info
- Trade history
- Real-time rates

#### Binance B2B Features
- Merchant account management
- Payment links (QR codes)
- Bulk payment processing
- Settlement management
- Withdrawal requests
- Transaction history
- Wallet balance
- Webhook configuration

#### Payment Gateways Added
| Region | New Gateways |
|--------|-------------|
| Crypto | Binance |
| Americas | BlueSnap, Chargify, PayU, EBANX, dLocal |
| Asia Pacific | KakaoPay, NaverPay, Toss, dPay, RakutenPay, Merpay, 7-Eleven |
| Europe | Giropay, Sofort, Przelewy24, Trustly, Multibanco, EPS |
| Africa | Interswitch, Paga, VoguePay, OrangeMoney, MtnMobileMoney, AirtelAfrica, Masary |
| Middle East | ArabBankPay, Checkout, HyperPay |
| South Asia | AmazonPay, FreeCharge, Mobikwik, AirtelMoney, SimPay |

### Testing
- **153 Unit Tests**
- **533 Assertions**
- **38 Binance-specific tests**

### Bug Fixes
- Fixed PaymentResponse constructor signature issues
- Fixed subscription response class paths
- Fixed date/time helper compatibility

---

## [1.0.0] - 2024-03-28

### 🎉 Major Release

#### Core Features
- **33+ Payment Gateways** - Full implementation across 8 regions
- **Unified API** - Single interface for all payment gateways
- **Multi-Currency** - Real-time currency conversion
- **B2B Payments** - Invoices, wire transfers, purchase orders
- **P2P Payments** - Send money, split bills, escrow
- **Subscriptions** - Recurring billing support
- **Webhooks** - Secure webhook handling with signature verification
- **Security** - Fraud detection, PCI compliance helpers

#### Payment Gateways

| Region | Gateways |
|--------|----------|
| Global | Stripe, PayPal |
| South Asia | bKash, Nagad, UPI, PhonePe, Paytm, JazzCash, Easypaisa |
| Middle East | PayTabs, Telr, Mada |
| Africa | Flutterwave, Paystack, M-Pesa, Fawry |
| Europe | Klarna, Adyen, iDEAL, Bancontact, SEPA |
| Asia Pacific | Alipay, WeChat Pay, PayPay, Line Pay, GrabPay |
| Americas | Square, Authorize.net, Moneris, MercadoPago, PagSeguro |
| Crypto | Bitcoin, Ethereum |

#### CLI Commands
- `payment:install` - Interactive installer
- `payment:list` - List gateways
- `payment:test` - Test gateway
- `payment:webhook` - Setup webhooks

#### Documentation
- Complete API Reference
- Implementation Guide
- Troubleshooting Guide
- Gateway-specific setup docs
- HTML documentation site

#### Assets
- CSS framework (payment.css)
- JavaScript SDK (payment.js)
- Card validation utilities
- Form handling components

### Changed
- Package renamed: `shamimstack/laravel-all-in-one-payment` → `shamimstack/wwwpay`
- Namespace: `ShamimStack\AllInOnePayment` → `ShamimStack\WwwPay`

### Breaking Changes
1. Update composer require:
   ```bash
   composer require shamimstack/wwwpay
   ```

2. Update namespace in code:
   ```php
   // Before
   use ShamimStack\AllInOnePayment\Facades\Payment;
   
   // After
   use ShamimStack\WwwPay\Facades\Payment;
   ```

---

## [0.1.0] - 2026-03-26

### Added
- Initial package structure
- Basic gateway implementations
- Payment Facade
- Unit tests (89 tests, 379 assertions)

---

## Upgrade Guide

### From v0.x to v1.0

```bash
# Update composer
composer require shamimstack/wwwpay:^1.0

# Update composer.json
# "shamimstack/laravel-all-in-one-payment" → "shamimstack/wwwpay"
```

```php
// Update all imports
// From: ShamimStack\AllInOnePayment\*
// To:   ShamimStack\WwwPay\*
```

---

## Links

- 📚 [Documentation](docs/index.html)
- 📖 [API Reference](docs/api-reference.md)
- 🚀 [Implementation Guide](docs/implementation.md)
- 🔧 [Troubleshooting](docs/troubleshooting.md)
- 🐛 [Issues](https://github.com/shamimstack/wwwpay/issues)
- 💬 [Discussions](https://github.com/shamimstack/wwwpay/discussions)

---

**MIT License** - Copyright (c) 2024 shamimstack
