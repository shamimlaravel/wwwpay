# SEPA Gateway Integration Guide

## Overview

SEPA (Single Euro Payments Area) enables seamless euro payments across Europe.

## Configuration

```env
SEPA_CREDITOR_NAME=Your Company Name
SEPA_CREDITOR_IBAN=your_iban
SEPA_CREDITOR_BIC=your_bic
SEPA_ENVIRONMENT=sandbox
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// SEPA Direct Debit
$response = Payment::gateway('sepa')->pay([
    'amount' => 100.00,
    'currency' => 'EUR',
    'iban' => 'DE89370400440532013000',
    'bic' => 'COBADEFFXXX',
    'customer_name' => 'Hans Mueller',
    'customer_email' => 'hans@example.de',
    'mandate_reference' => 'MANDATE-' . time(),
]);
```

## Payment Flow

1. Create SEPA mandate (one-time authorization)
2. Initiate direct debit
3. Funds credited in 1-2 business days

## Mandate Management

```php
// Create new mandate
$mandate = Payment::gateway('sepa')->createMandate([
    'iban' => 'DE89370400440532013000',
    'customer_name' => 'Hans Mueller',
]);

// Revoke mandate
Payment::gateway('sepa')->revokeMandate($mandateId);
```

## Webhook Events

- `sepa.mandate.created`
- `sepa.mandate.revoked`
- `sepa.payment.settled`
- `sepa.payment.failed`

## Supported Countries

Austria, Belgium, Bulgaria, Croatia, Cyprus, Czech Republic, Denmark, Estonia, Finland, France, Germany, Greece, Hungary, Iceland, Ireland, Italy, Latvia, Liechtenstein, Lithuania, Luxembourg, Malta, Netherlands, Norway, Poland, Portugal, Romania, Slovakia, Slovenia, Spain, Sweden, Switzerland, United Kingdom
