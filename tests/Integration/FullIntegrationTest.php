<?php

namespace ShamimStack\AllInOnePayment\Tests\Integration;

use ShamimStack\AllInOnePayment\Tests\TestCase;
use ShamimStack\AllInOnePayment\Models\Transaction;
use ShamimStack\AllInOnePayment\Models\Subscription;
use ShamimStack\AllInOnePayment\B2B\B2BPayment;
use ShamimStack\AllInOnePayment\P2P\P2PPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FullIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function defineDatabaseMigrationsAfterRefresh($database)
    {
        $database->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    public function test_can_create_transaction_model()
    {
        $transaction = Transaction::create([
            'gateway' => 'stripe',
            'gateway_transaction_id' => 'txn_test_123',
            'order_id' => 'order_456',
            'amount' => 100.50,
            'currency' => 'USD',
            'status' => Transaction::STATUS_PENDING,
            'type' => Transaction::TYPE_PAYMENT,
            'customer_email' => 'test@example.com',
        ]);

        $this->assertNotNull($transaction->id);
        $this->assertEquals('stripe', $transaction->gateway);
        $this->assertEquals(100.50, $transaction->amount);
    }

    public function test_transaction_status_workflow()
    {
        $transaction = Transaction::create([
            'gateway' => 'stripe',
            'gateway_transaction_id' => 'txn_test_456',
            'amount' => 200.00,
            'currency' => 'USD',
            'status' => Transaction::STATUS_PENDING,
            'type' => Transaction::TYPE_PAYMENT,
        ]);

        $transaction->markAsCompleted();
        $this->assertTrue($transaction->isSuccessful());
        $this->assertNotNull($transaction->completed_at);

        $transaction->processRefund(50.00);
        $this->assertEquals(Transaction::STATUS_PARTIALLY_REFUNDED, $transaction->status);
        $this->assertEquals(50.00, $transaction->refunded_amount);
    }

    public function test_transaction_scopes()
    {
        Transaction::create([
            'gateway' => 'stripe',
            'gateway_transaction_id' => 'txn_scope_1',
            'amount' => 100.00,
            'currency' => 'USD',
            'status' => Transaction::STATUS_COMPLETED,
            'type' => Transaction::TYPE_PAYMENT,
            'customer_id' => 'cust_1',
        ]);

        Transaction::create([
            'gateway' => 'paypal',
            'gateway_transaction_id' => 'txn_scope_2',
            'amount' => 200.00,
            'currency' => 'USD',
            'status' => Transaction::STATUS_FAILED,
            'type' => Transaction::TYPE_PAYMENT,
            'customer_id' => 'cust_2',
        ]);

        $this->assertEquals(1, Transaction::successful()->count());
        $this->assertEquals(1, Transaction::byGateway('stripe')->count());
        $this->assertEquals(1, Transaction::byCustomer('cust_1')->count());
    }

    public function test_can_create_subscription_model()
    {
        $subscription = Subscription::create([
            'gateway' => 'stripe',
            'gateway_subscription_id' => 'sub_test_123',
            'status' => 'active',
            'plan_id' => 'plan_monthly',
            'customer_id' => 'cust_789',
        ]);

        $this->assertNotNull($subscription->id);
        $this->assertTrue($subscription->isActive());
    }

    public function test_subscription_lifecycle()
    {
        $subscription = Subscription::create([
            'gateway' => 'stripe',
            'gateway_subscription_id' => 'sub_lifecycle_123',
            'status' => 'active',
            'plan_id' => 'plan_monthly',
            'customer_id' => 'cust_lifecycle',
            'start_date' => now(),
        ]);

        $this->assertTrue($subscription->isActive());
        
        $subscription->cancel('Customer requested');
        
        $this->assertTrue($subscription->isCancelled());
        $this->assertFalse($subscription->isActive());
    }

    public function test_b2b_payment_invoice_creation()
    {
        $b2b = new B2BPayment();
        
        $invoice = $b2b->createInvoice([
            'amount' => 1000.00,
            'currency' => 'USD',
            'recipient_name' => 'Acme Corp',
            'recipient_email' => 'billing@acme.com',
            'recipient_company' => 'Acme Corporation',
            'recipient_tax_id' => 'TAX123456',
            'items' => [
                ['description' => 'Service A', 'amount' => 500],
                ['description' => 'Service B', 'amount' => 500],
            ],
        ]);

        $this->assertArrayHasKey('invoice_id', $invoice);
        $this->assertEquals(1000.00, $invoice['amount']);
        $this->assertEquals('draft', $invoice['status']);
        $this->assertEquals('Acme Corporation', $invoice['recipient']['company']);
    }

    public function test_b2b_wire_transfer()
    {
        $b2b = new B2BPayment();
        
        $response = $b2b->processWireTransfer([
            'amount' => 5000.00,
            'currency' => 'USD',
            'bank_name' => 'Chase Bank',
            'account_name' => 'My Company Inc',
            'account_number' => '1234567890',
            'routing_number' => '021000021',
            'swift_code' => 'CHASUS33',
        ]);

        $this->assertTrue($response->isSuccessful());
        $this->assertArrayHasKey('instructions', $response->getData());
    }

    public function test_b2b_ach_payment()
    {
        $b2b = new B2BPayment();
        
        $response = $b2b->processACHPayment([
            'amount' => 2500.00,
            'routing_number' => '021000021',
            'account_number' => '1234567890',
        ]);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('processing', $response->getData()['status']);
    }

    public function test_b2b_bulk_payment()
    {
        $b2b = new B2BPayment();
        
        $payments = [
            ['vendor_id' => 'V001', 'amount' => 100.00, 'reference' => 'PO-001'],
            ['vendor_id' => 'V002', 'amount' => 200.00, 'reference' => 'PO-002'],
            ['vendor_id' => 'V003', 'amount' => 300.00, 'reference' => 'PO-003'],
        ];

        $result = $b2b->bulkPayment($payments);

        $this->assertArrayHasKey('batch_id', $result);
        $this->assertEquals(3, $result['total_payments']);
        $this->assertEquals(3, $result['successful']);
        $this->assertEquals(600.00, $result['total_amount']);
    }

    public function test_p2p_send_money()
    {
        $p2p = new P2PPayment();
        
        $response = $p2p->sendMoney([
            'amount' => 100.00,
            'recipient_id' => 'user_456',
            'sender_id' => 'user_123',
            'currency' => 'USD',
        ]);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('send', $response->getData()['type']);
        $this->assertEquals(100.00, $response->getData()['amount']);
    }

    public function test_p2p_request_money()
    {
        $p2p = new P2PPayment();
        
        $response = $p2p->requestMoney([
            'amount' => 50.00,
            'sender_id' => 'user_789',
            'message' => 'Lunch money',
        ]);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('request', $response->getData()['type']);
        $this->assertEquals('pending', $response->getData()['status']);
    }

    public function test_p2p_split_payment()
    {
        $p2p = new P2PPayment();
        
        $response = $p2p->splitPayment([
            'amount' => 300.00,
            'currency' => 'USD',
            'splits' => [
                ['recipient_id' => 'user_1', 'amount' => 100.00],
                ['recipient_id' => 'user_2', 'amount' => 100.00],
                ['recipient_id' => 'user_3', 'amount' => 100.00],
            ],
        ]);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('split', $response->getData()['type']);
        $this->assertCount(3, $response->getData()['splits']);
    }

    public function test_p2p_escrow()
    {
        $p2p = new P2PPayment();
        
        $response = $p2p->escrowPayment([
            'amount' => 500.00,
            'recipient_id' => 'user_seller',
            'sender_id' => 'user_buyer',
            'conditions' => 'Delivery confirmation required',
        ]);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('held', $response->getData()['status']);
        $this->assertArrayHasKey('release_conditions', $response->getData());
    }

    public function test_p2p_international_transfer()
    {
        $p2p = new P2PPayment();
        
        $response = $p2p->internationalTransfer([
            'amount' => 1000.00,
            'currency' => 'USD',
            'recipient_id' => 'user_intl',
            'recipient_currency' => 'EUR',
        ]);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('international', $response->getData()['type']);
        $this->assertEquals('EUR', $response->getData()['recipient_currency']);
        $this->assertArrayHasKey('exchange_rate', $response->getData());
    }

    public function test_gateway_integration_with_transaction()
    {
        $transaction = Transaction::create([
            'gateway' => 'stripe',
            'gateway_transaction_id' => 'pi_test_123',
            'order_id' => 'order_int_test',
            'amount' => 150.00,
            'currency' => 'USD',
            'status' => Transaction::STATUS_COMPLETED,
            'type' => Transaction::TYPE_PAYMENT,
            'customer_email' => 'customer@test.com',
        ]);

        $subscription = Subscription::create([
            'gateway' => 'stripe',
            'gateway_subscription_id' => 'sub_int_123',
            'status' => 'active',
            'plan_id' => 'plan_pro',
            'customer_id' => $transaction->customer_email,
        ]);

        $this->assertEquals($transaction->customer_email, $subscription->customer_id);
    }
}
