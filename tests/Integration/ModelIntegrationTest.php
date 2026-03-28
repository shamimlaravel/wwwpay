<?php

namespace ShamimStack\AllInOnePayment\Tests\Integration;

use ShamimStack\AllInOnePayment\Tests\TestCase;
use ShamimStack\AllInOnePayment\Models\Transaction;
use ShamimStack\AllInOnePayment\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModelIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_transaction()
    {
        $transaction = Transaction::create([
            'gateway' => 'stripe',
            'gateway_transaction_id' => 'txn_123',
            'order_id' => 'order_456',
            'amount' => 100.50,
            'currency' => 'USD',
            'status' => Transaction::STATUS_PENDING,
            'type' => Transaction::TYPE_PAYMENT,
            'customer_id' => 'cust_789',
            'customer_email' => 'test@example.com',
            'description' => 'Test payment',
        ]);

        $this->assertNotNull($transaction->id);
        $this->assertEquals('stripe', $transaction->gateway);
        $this->assertEquals('txn_123', $transaction->gateway_transaction_id);
        $this->assertEquals(100.50, $transaction->amount);
        $this->assertEquals('USD', $transaction->currency);
        $this->assertEquals(Transaction::STATUS_PENDING, $transaction->status);
    }

    public function test_transaction_status_methods()
    {
        $transaction = Transaction::create([
            'gateway' => 'stripe',
            'gateway_transaction_id' => 'txn_123',
            'order_id' => 'order_456',
            'amount' => 100.50,
            'currency' => 'USD',
            'status' => Transaction::STATUS_PENDING,
            'type' => Transaction::TYPE_PAYMENT,
        ]);

        $this->assertTrue($transaction->isPending());
        $this->assertFalse($transaction->isSuccessful());
        $this->assertFalse($transaction->isFailed());
        $this->assertFalse($transaction->isRefunded());
        $this->assertFalse($transaction->isCancelled());
    }

    public function test_transaction_mark_as_completed()
    {
        $transaction = Transaction::create([
            'gateway' => 'stripe',
            'gateway_transaction_id' => 'txn_123',
            'order_id' => 'order_456',
            'amount' => 100.50,
            'currency' => 'USD',
            'status' => Transaction::STATUS_PROCESSING,
            'type' => Transaction::TYPE_PAYMENT,
        ]);

        $result = $transaction->markAsCompleted();

        $this->assertTrue($result);
        $this->assertEquals(Transaction::STATUS_COMPLETED, $transaction->status);
        $this->assertNotNull($transaction->completed_at);
        $this->assertTrue($transaction->isSuccessful());
    }

    public function test_transaction_process_refund()
    {
        $transaction = Transaction::create([
            'gateway' => 'stripe',
            'gateway_transaction_id' => 'txn_123',
            'order_id' => 'order_456',
            'amount' => 100.00,
            'currency' => 'USD',
            'status' => Transaction::STATUS_COMPLETED,
            'type' => Transaction::TYPE_PAYMENT,
        ]);

        $transaction->processRefund(50.00);

        $this->assertEquals(Transaction::STATUS_PARTIALLY_REFUNDED, $transaction->status);
        $this->assertEquals(50.00, $transaction->refunded_amount);
        $this->assertNotNull($transaction->refunded_at);
        $this->assertEquals(50.00, $transaction->getRefundableAmount());
    }

    public function test_transaction_full_refund()
    {
        $transaction = Transaction::create([
            'gateway' => 'stripe',
            'gateway_transaction_id' => 'txn_123',
            'order_id' => 'order_456',
            'amount' => 100.00,
            'currency' => 'USD',
            'status' => Transaction::STATUS_COMPLETED,
            'type' => Transaction::TYPE_PAYMENT,
        ]);

        $transaction->processRefund(100.00);

        $this->assertEquals(Transaction::STATUS_REFUNDED, $transaction->status);
        $this->assertEquals(100.00, $transaction->refunded_amount);
        $this->assertEquals(0.00, $transaction->getRefundableAmount());
    }

    public function test_transaction_cancel()
    {
        $transaction = Transaction::create([
            'gateway' => 'stripe',
            'gateway_transaction_id' => 'txn_123',
            'order_id' => 'order_456',
            'amount' => 100.50,
            'currency' => 'USD',
            'status' => Transaction::STATUS_PENDING,
            'type' => Transaction::TYPE_PAYMENT,
        ]);

        $transaction->cancel();

        $this->assertEquals(Transaction::STATUS_CANCELLED, $transaction->status);
        $this->assertNotNull($transaction->cancelled_at);
        $this->assertTrue($transaction->isCancelled());
    }

    public function test_transaction_scopes()
    {
        Transaction::create([
            'gateway' => 'stripe',
            'gateway_transaction_id' => 'txn_1',
            'order_id' => 'order_1',
            'amount' => 100.00,
            'currency' => 'USD',
            'status' => Transaction::STATUS_COMPLETED,
            'type' => Transaction::TYPE_PAYMENT,
            'customer_id' => 'cust_1',
        ]);

        Transaction::create([
            'gateway' => 'paypal',
            'gateway_transaction_id' => 'txn_2',
            'order_id' => 'order_2',
            'amount' => 200.00,
            'currency' => 'USD',
            'status' => Transaction::STATUS_PENDING,
            'type' => Transaction::TYPE_PAYMENT,
            'customer_id' => 'cust_2',
        ]);

        $this->assertEquals(1, Transaction::successful()->count());
        $this->assertEquals(1, Transaction::pending()->count());
        $this->assertEquals(1, Transaction::byGateway('stripe')->count());
        $this->assertEquals(1, Transaction::byCustomer('cust_1')->count());
    }

    public function test_can_create_subscription()
    {
        $subscription = Subscription::create([
            'gateway' => 'stripe',
            'gateway_subscription_id' => 'sub_123',
            'status' => 'active',
            'plan_id' => 'plan_basic',
            'customer_id' => 'cust_789',
            'start_date' => now(),
            'data' => ['interval' => 'monthly'],
        ]);

        $this->assertNotNull($subscription->id);
        $this->assertEquals('stripe', $subscription->gateway);
        $this->assertEquals('sub_123', $subscription->gateway_subscription_id);
        $this->assertEquals('active', $subscription->status);
        $this->assertTrue($subscription->isActive());
    }

    public function test_subscription_is_active()
    {
        $subscription = Subscription::create([
            'gateway' => 'stripe',
            'gateway_subscription_id' => 'sub_123',
            'status' => 'active',
            'plan_id' => 'plan_basic',
            'customer_id' => 'cust_789',
            'start_date' => now(),
            'end_date' => null,
        ]);

        $this->assertTrue($subscription->isActive());
    }

    public function test_subscription_cancel()
    {
        $subscription = Subscription::create([
            'gateway' => 'stripe',
            'gateway_subscription_id' => 'sub_123',
            'status' => 'active',
            'plan_id' => 'plan_basic',
            'customer_id' => 'cust_789',
            'start_date' => now(),
        ]);

        $subscription->cancel('Customer requested');

        $this->assertEquals('cancelled', $subscription->status);
        $this->assertNotNull($subscription->end_date);
        $this->assertTrue($subscription->isCancelled());
    }
}
