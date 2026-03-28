# Binance Payment Gateway

Binance P2P (Peer-to-Peer) and B2B (Business-to-Business) payment gateway for WwwPay.

## Installation

```bash
composer require shamimstack/wwwpay
```

## Configuration

Add to `config/payment.php`:

```php
'gateways' => [
    'binance' => [
        'api_key' => env('BINANCE_API_KEY'),
        'api_secret' => env('BINANCE_API_SECRET'),
        'merchant_id' => env('BINANCE_MERCHANT_ID'),
        'test_mode' => env('BINANCE_TEST_MODE', false),
    ],
],
```

## Environment Variables

```env
BINANCE_API_KEY=your_api_key
BINANCE_API_SECRET=your_api_secret
BINANCE_MERCHANT_ID=your_merchant_id
BINANCE_TEST_MODE=false
```

## Usage

### Gateway Basic Usage

```php
use ShamimStack\WwwPay\Facades\Payment;

// Create P2P order
$response = Payment::gateway('binance')->pay([
    'amount' => 100,
    'currency' => 'USDT',
    'fiat' => 'USD',
    'side' => 'BUY',
]);

// Check if successful
if ($response->isSuccessful()) {
    $orderId = $response->getTransactionId();
    $data = $response->getData();
}
```

### Access P2P Services

```php
$binance = Payment::gateway('binance');
$p2p = $binance->p2p();

// Create advertisement
$ad = $p2p->createAd([
    'asset' => 'USDT',
    'fiat' => 'USD',
    'price' => 1.00,
    'available_amount' => 1000,
    'payment_methods' => ['BANK', 'ALIPAY'],
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

// Appeal dispute
$p2p->appeal($orderId, [
    'reason' => 'payment_not_received',
    'description' => 'Payment was not received',
]);
```

### Access B2B Services

```php
$b2b = Payment::gateway('binance')->b2b();

// Create merchant account
$merchant = $b2b->createMerchantAccount([
    'business_name' => 'My Business',
    'business_type' => 'ecommerce',
    'contact_email' => 'business@example.com',
]);

// Create payment link
$link = $b2b->createPaymentLink([
    'amount' => 100,
    'currency' => 'USDT',
    'description' => 'Order #12345',
    'expiry_days' => 7,
]);

// Process bulk payments
$batch = $b2b->processBulkPayment([
    'payments' => [
        ['recipient_id' => 'user1', 'amount' => 100],
        ['recipient_id' => 'user2', 'amount' => 200],
    ],
    'currency' => 'USDT',
]);

// Get settlement info
$settlements = $b2b->getSettlement();

// Request withdrawal
$withdrawal = $b2b->requestWithdrawal([
    'amount' => 1000,
    'currency' => 'USDT',
    'payment_method' => 'BANK',
    'bank_name' => 'My Bank',
    'account_number' => '1234567890',
]);
```

## REST API Endpoints

### Payment
- `POST /api/payment/binance/pay` - Create P2P payment
- `POST /api/payment/binance/refund/{orderId}` - Process refund
- `POST /api/payment/binance/cancel/{orderId}` - Cancel order

### Webhooks
- `POST /api/payment/binance/webhook` - Handle webhooks

### P2P Operations
- `POST /api/payment/binance/p2p/ads` - Create ad
- `PUT /api/payment/binance/p2p/ads/{adId}` - Update ad
- `DELETE /api/payment/binance/p2p/ads/{adId}` - Delete ad
- `GET /api/payment/binance/p2p/ads/{adId}` - Get ad
- `GET /api/payment/binance/p2p/ads` - List ads
- `POST /api/payment/binance/p2p/orders` - Place order
- `GET /api/payment/binance/p2p/orders/{orderId}` - Get order
- `GET /api/payment/binance/p2p/orders` - List orders
- `POST /api/payment/binance/p2p/orders/{orderId}/confirm` - Confirm payment
- `POST /api/payment/binance/p2p/orders/{orderId}/release` - Release crypto
- `POST /api/payment/binance/p2p/orders/{orderId}/appeal` - Appeal
- `GET /api/payment/binance/p2p/trade-history` - Trade history
- `GET /api/payment/binance/p2p/rates` - Get rates

### B2B Operations
- `POST /api/payment/binance/b2b/merchant` - Create merchant
- `GET /api/payment/binance/b2b/merchant` - Get merchant info
- `POST /api/payment/binance/b2b/payment-links` - Create payment link
- `GET /api/payment/binance/b2b/payment-links/{linkId}` - Get payment link
- `GET /api/payment/binance/b2b/payment-links` - List payment links
- `DELETE /api/payment/binance/b2b/payment-links/{linkId}` - Delete payment link
- `POST /api/payment/binance/b2b/bulk-payment` - Process bulk payment
- `GET /api/payment/binance/b2b/bulk-payment/{batchId}` - Get bulk payment status
- `GET /api/payment/binance/b2b/settlements` - Get settlements
- `POST /api/payment/binance/b2b/withdrawal` - Request withdrawal
- `GET /api/payment/binance/b2b/transactions` - Transaction history
- `GET /api/payment/binance/b2b/balance` - Wallet balance
- `POST /api/payment/binance/b2b/webhooks` - Create webhook
- `GET /api/payment/binance/b2b/webhooks/events` - Available webhook events

## Supported Assets

- USDT
- BTC
- ETH
- BNB
- BUSD
- And more...

## Supported Fiat Currencies

- USD, EUR, GBP
- BRL, ARS, MXN
- PHP, VND, THB
- NGN, KES, ZAR
- AED, SAR, and more...

## Webhook Events

```php
// In your webhook handler
$binance = Payment::gateway('binance');
$binance->handleWebhook($request);

// Supported events:
// - P2P_ORDER_CREATED
// - P2P_ORDER_PAID
// - P2P_ORDER_RELEASED
// - P2P_ORDER_CANCELLED
// - P2P_ORDER_APPEALED
// - P2P_ORDER_DISPUTE
```

## Subscription Support

```php
$subscription = Payment::gateway('binance')->subscribe([
    'plan_id' => 'monthly_trade',
    'customer_id' => 'customer_123',
    'asset' => 'USDT',
    'fiat' => 'USD',
    'amount' => 100,
    'frequency' => 'monthly',
]);

$subscriptionId = $subscription->getGatewaySubscriptionId();
$status = $subscription->getStatus();
```

## Error Handling

```php
try {
    $response = Payment::gateway('binance')->pay($data);
    
    if (!$response->isSuccessful()) {
        $error = $response->getErrorMessage();
        // Handle error
    }
} catch (\Exception $e) {
    // Handle exception
}
```

## Testing

```php
// In test environment
$gateway = new BinanceGateway([
    'test_mode' => true,
]);
```

## Security

- Always use HTTPS for API calls
- Store API keys securely in environment variables
- Verify webhook signatures
- Use IP whitelisting when possible
- Implement rate limiting for your application

## Rate Limits

- P2P API: 120 requests/minute
- B2B API: 60 requests/minute
- General API: 1200 requests/minute

## Support

For issues or questions:
- GitHub Issues: https://github.com/shamimlaravel/wwwpay/issues
- Email: support@example.com
