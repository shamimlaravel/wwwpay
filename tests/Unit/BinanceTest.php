<?php

namespace ShamimStack\WwwPay\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ShamimStack\WwwPay\Gateways\Crypto\BinanceGateway;
use ShamimStack\WwwPay\P2P\BinanceP2P;
use ShamimStack\WwwPay\B2B\BinanceB2B;

class BinanceTest extends TestCase
{
    public function test_binance_gateway_can_be_instantiated()
    {
        $gateway = new BinanceGateway([]);
        $this->assertInstanceOf(BinanceGateway::class, $gateway);
    }

    public function test_binance_gateway_has_correct_name()
    {
        $gateway = new BinanceGateway([]);
        $this->assertEquals('Binance', $gateway->getName());
    }

    public function test_binance_gateway_supports_subscriptions()
    {
        $gateway = new BinanceGateway([]);
        $this->assertTrue($gateway->supportsSubscriptions());
    }

    public function test_binance_gateway_has_p2p_method()
    {
        $gateway = new BinanceGateway([]);
        $this->assertInstanceOf(BinanceP2P::class, $gateway->p2p());
    }

    public function test_binance_gateway_has_b2b_method()
    {
        $gateway = new BinanceGateway([]);
        $this->assertInstanceOf(BinanceB2B::class, $gateway->b2b());
    }

    public function test_binance_gateway_pay_returns_response()
    {
        $gateway = new BinanceGateway([]);
        $response = $gateway->pay([
            'amount' => 100,
            'currency' => 'USDT',
            'fiat' => 'USD',
            'side' => 'BUY',
        ]);

        $this->assertInstanceOf(\ShamimStack\WwwPay\Contracts\PaymentResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
    }

    public function test_binance_gateway_refund_returns_response()
    {
        $gateway = new BinanceGateway([]);
        $response = $gateway->refund('BN_ORDER_123', 50);

        $this->assertInstanceOf(\ShamimStack\WwwPay\Contracts\PaymentResponse::class, $response);
    }

    public function test_binance_gateway_cancel_returns_response()
    {
        $gateway = new BinanceGateway([]);
        $response = $gateway->cancel('BN_ORDER_123');

        $this->assertInstanceOf(\ShamimStack\WwwPay\Contracts\PaymentResponse::class, $response);
    }

    public function test_binance_gateway_verify()
    {
        $gateway = new BinanceGateway([]);
        
        $this->assertTrue($gateway->verify(['email' => 'test@example.com']));
        $this->assertTrue($gateway->verify(['phone' => '+1234567890']));
        $this->assertTrue($gateway->verify(['binance_id' => 'user123']));
        $this->assertFalse($gateway->verify([]));
    }

    public function test_binance_gateway_subscribe()
    {
        $gateway = new BinanceGateway([]);
        $subscription = $gateway->subscribe([
            'plan_id' => 'plan_123',
            'customer_id' => 'customer_456',
            'asset' => 'USDT',
            'fiat' => 'USD',
            'amount' => 100,
        ]);

        $this->assertInstanceOf(\ShamimStack\WwwPay\Contracts\Subscription::class, $subscription);
        $this->assertNotEmpty($subscription->getGatewaySubscriptionId());
    }

    public function test_binance_gateway_unsubscribe()
    {
        $gateway = new BinanceGateway([]);
        $result = $gateway->unsubscribe('BN_SUB_123');
        $this->assertTrue($result);
    }

    public function test_binance_p2p_create_ad()
    {
        $p2p = new BinanceP2P([]);
        $ad = $p2p->createAd([
            'asset' => 'USDT',
            'fiat' => 'USD',
            'price' => 1.00,
            'available_amount' => 1000,
            'payment_methods' => ['BANK'],
        ]);

        $this->assertArrayHasKey('ad_id', $ad);
        $this->assertEquals('USDT', $ad['asset']);
        $this->assertEquals('USD', $ad['fiat']);
    }

    public function test_binance_p2p_place_order()
    {
        $p2p = new BinanceP2P([]);
        $response = $p2p->placeOrder([
            'ad_id' => 'AD_123',
            'amount' => 100,
            'asset' => 'USDT',
            'fiat' => 'USD',
            'side' => 'BUY',
        ]);

        $this->assertInstanceOf(\ShamimStack\WwwPay\Contracts\PaymentResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
    }

    public function test_binance_p2p_place_order_requires_fields()
    {
        $p2p = new BinanceP2P([]);
        $response = $p2p->placeOrder([]);

        $this->assertFalse($response->isSuccessful());
    }

    public function test_binance_p2p_confirm_payment()
    {
        $p2p = new BinanceP2P([]);
        $response = $p2p->confirmPayment('BN_P2P_123');

        $this->assertInstanceOf(\ShamimStack\WwwPay\Contracts\PaymentResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
    }

    public function test_binance_p2p_release_crypto()
    {
        $p2p = new BinanceP2P([]);
        $response = $p2p->releaseCrypto('BN_P2P_123');

        $this->assertInstanceOf(\ShamimStack\WwwPay\Contracts\PaymentResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
    }

    public function test_binance_p2p_get_order()
    {
        $p2p = new BinanceP2P([]);
        $order = $p2p->getOrder('BN_P2P_123');

        $this->assertArrayHasKey('order_id', $order);
        $this->assertArrayHasKey('status', $order);
    }

    public function test_binance_p2p_list_orders()
    {
        $p2p = new BinanceP2P([]);
        $result = $p2p->listOrders(['limit' => 5]);

        $this->assertArrayHasKey('orders', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertIsArray($result['orders']);
    }

    public function test_binance_p2p_appeal()
    {
        $p2p = new BinanceP2P([]);
        $appeal = $p2p->appeal('BN_P2P_123', [
            'reason' => 'payment_not_received',
            'description' => 'Payment was not received',
        ]);

        $this->assertArrayHasKey('appeal_id', $appeal);
        $this->assertEquals('BN_P2P_123', $appeal['order_id']);
    }

    public function test_binance_p2p_get_user_info()
    {
        $p2p = new BinanceP2P([]);
        $info = $p2p->getUserInfo('user_123');

        $this->assertArrayHasKey('user_id', $info);
        $this->assertArrayHasKey('nick_name', $info);
        $this->assertArrayHasKey('completion_rate', $info);
    }

    public function test_binance_p2p_get_merchant_info()
    {
        $p2p = new BinanceP2P([]);
        $info = $p2p->getMerchantInfo();

        $this->assertArrayHasKey('merchant_id', $info);
        $this->assertArrayHasKey('status', $info);
    }

    public function test_binance_p2p_get_trade_history()
    {
        $p2p = new BinanceP2P([]);
        $result = $p2p->getTradeHistory(['limit' => 10]);

        $this->assertArrayHasKey('trades', $result);
        $this->assertArrayHasKey('total', $result);
    }

    public function test_binance_p2p_get_rates()
    {
        $p2p = new BinanceP2P([]);
        $rates = $p2p->getRates(['asset' => 'USDT', 'fiat' => 'USD']);

        $this->assertArrayHasKey('asset', $rates);
        $this->assertArrayHasKey('fiat', $rates);
        $this->assertArrayHasKey('buy_price', $rates);
        $this->assertArrayHasKey('sell_price', $rates);
    }

    public function test_binance_b2b_create_merchant_account()
    {
        $b2b = new BinanceB2B([]);
        $merchant = $b2b->createMerchantAccount([
            'business_name' => 'Test Business',
            'business_type' => 'ecommerce',
            'contact_email' => 'test@example.com',
        ]);

        $this->assertArrayHasKey('merchant_id', $merchant);
        $this->assertEquals('Test Business', $merchant['business_name']);
    }

    public function test_binance_b2b_get_merchant_account()
    {
        $b2b = new BinanceB2B([]);
        $merchant = $b2b->getMerchantAccount();

        $this->assertArrayHasKey('merchant_id', $merchant);
        $this->assertArrayHasKey('status', $merchant);
    }

    public function test_binance_b2b_create_payment_link()
    {
        $b2b = new BinanceB2B([]);
        $link = $b2b->createPaymentLink([
            'amount' => 100,
            'currency' => 'USDT',
            'description' => 'Test payment',
        ]);

        $this->assertArrayHasKey('link_id', $link);
        $this->assertArrayHasKey('url', $link);
        $this->assertArrayHasKey('qr_code_url', $link);
    }

    public function test_binance_b2b_get_payment_link()
    {
        $b2b = new BinanceB2B([]);
        $link = $b2b->getPaymentLink('PL_123');

        $this->assertArrayHasKey('link_id', $link);
    }

    public function test_binance_b2b_list_payment_links()
    {
        $b2b = new BinanceB2B([]);
        $result = $b2b->listPaymentLinks(['limit' => 10]);

        $this->assertArrayHasKey('links', $result);
        $this->assertArrayHasKey('total', $result);
    }

    public function test_binance_b2b_delete_payment_link()
    {
        $b2b = new BinanceB2B([]);
        $result = $b2b->deletePaymentLink('PL_123');
        $this->assertTrue($result);
    }

    public function test_binance_b2b_process_bulk_payment()
    {
        $b2b = new BinanceB2B([]);
        $result = $b2b->processBulkPayment([
            'payments' => [
                ['recipient_id' => 'user1', 'amount' => 100],
                ['recipient_id' => 'user2', 'amount' => 200],
            ],
            'currency' => 'USDT',
        ]);

        $this->assertArrayHasKey('batch_id', $result);
        $this->assertEquals(2, $result['total_payments']);
        $this->assertEquals(2, $result['successful']);
    }

    public function test_binance_b2b_get_bulk_payment_status()
    {
        $b2b = new BinanceB2B([]);
        $status = $b2b->getBulkPaymentStatus('BATCH_123');

        $this->assertArrayHasKey('batch_id', $status);
        $this->assertArrayHasKey('status', $status);
    }

    public function test_binance_b2b_get_settlement()
    {
        $b2b = new BinanceB2B([]);
        $result = $b2b->getSettlement(['limit' => 10]);

        $this->assertArrayHasKey('settlements', $result);
        $this->assertArrayHasKey('pending_amount', $result);
        $this->assertArrayHasKey('available_amount', $result);
    }

    public function test_binance_b2b_request_withdrawal()
    {
        $b2b = new BinanceB2B([]);
        $withdrawal = $b2b->requestWithdrawal([
            'amount' => 1000,
            'currency' => 'USDT',
            'payment_method' => 'BANK',
            'bank_name' => 'Test Bank',
            'account_number' => '1234567890',
        ]);

        $this->assertArrayHasKey('withdrawal_id', $withdrawal);
        $this->assertEquals(1000, $withdrawal['amount']);
    }

    public function test_binance_b2b_get_transaction_history()
    {
        $b2b = new BinanceB2B([]);
        $result = $b2b->getTransactionHistory(['limit' => 20]);

        $this->assertArrayHasKey('transactions', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('total_volume', $result);
    }

    public function test_binance_b2b_get_wallet_balance()
    {
        $b2b = new BinanceB2B([]);
        $balance = $b2b->getWalletBalance();

        $this->assertArrayHasKey('total_balance', $balance);
        $this->assertArrayHasKey('available_balance', $balance);
        $this->assertArrayHasKey('locked_balance', $balance);
    }

    public function test_binance_b2b_create_webhook()
    {
        $b2b = new BinanceB2B([]);
        $webhook = $b2b->createWebhook([
            'url' => 'https://example.com/webhook',
            'events' => ['order_completed', 'settlement_completed'],
        ]);

        $this->assertArrayHasKey('webhook_id', $webhook);
        $this->assertArrayHasKey('secret', $webhook);
    }

    public function test_binance_b2b_get_webhook_events()
    {
        $b2b = new BinanceB2B([]);
        $events = $b2b->getWebhookEvents();

        $this->assertArrayHasKey('events', $events);
        $this->assertContains('p2p_order_released', $events['events']);
        $this->assertContains('settlement_completed', $events['events']);
    }

    public function test_binance_b2b_get_api_keys()
    {
        $b2b = new BinanceB2B([]);
        $keys = $b2b->getApiKeys();

        $this->assertArrayHasKey('api_key', $keys);
        $this->assertArrayHasKey('api_secret', $keys);
        $this->assertArrayHasKey('permissions', $keys);
    }
}
