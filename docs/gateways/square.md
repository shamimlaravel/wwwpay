# Square Gateway Integration Guide

## Overview

Square provides payment solutions for businesses in the US, Canada, UK, Australia, and Japan.

## Configuration

```env
SQUARE_APPLICATION_ID=your_application_id
SQUARE_ACCESS_TOKEN=your_access_token
SQUARE_LOCATION_ID=your_location_id
SQUARE_ENVIRONMENT=sandbox
```

## Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// Square Payment
$response = Payment::gateway('square')->pay([
    'amount' => 10000, // In cents
    'currency' => 'USD',
    'idempotency_key' => uniqid(),
    'source_id' => $nonceFromClient, // Card nonce from Square Web Payments SDK
    'reference_id' => 'ORDER_' . time(),
]);
```

## Card Payments

```php
// Initialize Square payment form
$session = Payment::gateway('square')->createPaymentSession([
    'currency' => 'USD',
    'amount' => 10000,
]);
```

## Web Payments SDK Integration

```html
<!-- Include Square Web Payments SDK -->
<div id="card-container"></div>

<script>
    const payments = Square.payments(applicationId, locationId);
    const card = await payments.card();
    await card.attach('#card-container');
    
    const result = await card.tokenize();
    if (result.status === 'OK') {
        // Send result.token to your server
    }
</script>
```

## In-Person Payments

```php
// Create terminal checkout
$response = Payment::gateway('square')->terminal([
    'amount' => 5000,
    'currency' => 'USD',
    'reference_id' => 'ORDER_' . time(),
]);
```

## Webhook Events

- `payment.created`
- `payment.completed`
- `payment.failed`
- `refund.created`
- `refund.completed`

## Testing

Use Square sandbox credentials from developer dashboard.
