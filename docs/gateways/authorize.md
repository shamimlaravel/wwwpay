# Authorize.net Gateway Integration Guide

## Overview

Authorize.net is a payment gateway serving US and Canadian merchants with credit card and e-check processing.

## Configuration

```env
AUTHORIZE_API_LOGIN_ID=your_login_id
AUTHORIZE_TRANSACTION_KEY=your_transaction_key
AUTHORIZE_ENVIRONMENT=sandbox
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// Credit Card Payment
$response = Payment::gateway('authorize')->pay([
    'amount' => 100.00,
    'currency' => 'USD',
    'card_number' => '4111111111111111',
    'expiration_date' => '12/2025',
    'card_code' => '123',
]);
```

## Card-Present vs Card-Not-Present

```php
// Card-Not-Present (default)
$response = Payment::gateway('authorize')->pay([
    'amount' => 100.00,
    'card_number' => '4111111111111111',
    'expiration_date' => '12/2025',
    'card_code' => '123',
]);

// Card-Present
$response = Payment::gateway('authorize')->pay([
    'amount' => 100.00,
    'card_number' => '4111111111111111',
    'expiration_date' => '12/2025',
    'card_code' => '123',
    'payment_type' => 'card_present',
]);
```

## Accept.js (Client-Side Tokenization)

```php
// Get client token
$clientToken = Payment::gateway('authorize')->getClientToken();
```

```html
<!-- Include Accept.js -->
<script src="https://js.authorize.net/v1/Accept.js"></script>

<form id="payment-form">
    <div id="cardNumber"></div>
    <div id="expirationDate"></div>
    <div id="cardCode"></div>
    <button type="submit">Pay</button>
</form>

<script>
    Accept.dispatchData({
        authData: {
            clientKey: 'your_client_key',
            apiLoginID: 'your_login_id'
        },
        cardData: {
            // From Accept.js UI elements
        }
    }, 'responseHandler');
</script>
```

## E-Check (ACH)

```php
$response = Payment::gateway('authorize')->pay([
    'amount' => 500.00,
    'bank_account' => [
        'account_type' => 'checking',
        'routing_number' => '121000358',
        'account_number' => '123456789',
        'name_on_account' => 'John Doe',
        'echeck_type' => 'WEB',
    ],
]);
```

## Webhook Events

- `net.authorize.payment.authcapture.created`
- `net.authorize.payment.refund.created`
- `net.authorize.customer.created`
- `net.authorize.customer.subscription.created`

## Testing

Use test account at https://developer.authorize.net/hello_world/testing/
