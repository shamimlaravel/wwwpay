# Changelog

All notable changes to **WwwPay** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
