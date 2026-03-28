<?php

namespace ShamimStack\WwwPay\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ShamimStack\WwwPay\B2B\B2BPayment;
use ShamimStack\WwwPay\P2P\P2PPayment;

class B2BAndP2PTest extends TestCase
{
    public function test_b2b_create_invoice()
    {
        $b2b = new B2BPayment();
        
        $invoice = $b2b->createInvoice([
            'amount' => 500.00,
            'currency' => 'USD',
            'recipient_name' => 'Test Company',
            'recipient_email' => 'test@company.com',
        ]);

        $this->assertArrayHasKey('invoice_id', $invoice);
        $this->assertEquals(500.00, $invoice['amount']);
        $this->assertEquals('draft', $invoice['status']);
        $this->assertEquals('Test Company', $invoice['recipient']['name']);
    }

    public function test_b2b_send_invoice()
    {
        $b2b = new B2BPayment();
        
        $response = $b2b->sendInvoice('INV_123', [
            'recipient_email' => 'test@example.com',
        ]);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('INV_123', $response->getTransactionId());
    }

    public function test_b2b_pay_invoice()
    {
        $b2b = new B2BPayment();
        
        $response = $b2b->payInvoice('INV_456', [
            'gateway' => 'stripe',
        ]);

        $this->assertTrue($response->isSuccessful());
    }

    public function test_b2b_create_purchase_order()
    {
        $b2b = new B2BPayment();
        
        $po = $b2b->createPurchaseOrder([
            'vendor_id' => 'V001',
            'vendor_name' => 'Acme Supplies',
            'amount' => 1000.00,
            'items' => [
                ['name' => 'Item A', 'qty' => 10, 'price' => 100.00],
            ],
        ]);

        $this->assertArrayHasKey('po_id', $po);
        $this->assertEquals('V001', $po['vendor_id']);
        $this->assertEquals('pending', $po['status']);
    }

    public function test_b2b_wire_transfer_requires_amount()
    {
        $b2b = new B2BPayment();
        
        $response = $b2b->processWireTransfer([]);
        $this->assertFalse($response->isSuccessful());
        $this->assertStringContainsString('required', $response->getErrorMessage());
    }

    public function test_b2b_wire_transfer_success()
    {
        $b2b = new B2BPayment();
        
        $response = $b2b->processWireTransfer([
            'amount' => 5000.00,
            'bank_account' => '1234567890',
        ]);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('wire_transfer', $response->getData()['method']);
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
        $this->assertEquals('ach', $response->getData()['method']);
    }

    public function test_b2b_corporate_card()
    {
        $b2b = new B2BPayment();
        
        $response = $b2b->processCorporateCard([
            'amount' => 1000.00,
            'card_number' => '4111111111111111',
        ]);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('visa', $response->getData()['card_type']);
    }

    public function test_b2b_vendor_payment()
    {
        $b2b = new B2BPayment();
        
        $response = $b2b->processVendorPayment([
            'amount' => 500.00,
            'vendor_id' => 'VENDOR_001',
        ]);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('vendor_payment', $response->getData()['method']);
    }

    public function test_b2b_payment_link()
    {
        $b2b = new B2BPayment();
        
        $link = $b2b->requestPaymentLink([
            'amount' => 200.00,
            'currency' => 'USD',
            'description' => 'Invoice payment',
        ]);

        $this->assertArrayHasKey('link_id', $link);
        $this->assertArrayHasKey('url', $link);
        $this->assertEquals(200.00, $link['amount']);
    }

    public function test_b2b_bulk_payment()
    {
        $b2b = new B2BPayment();
        
        $payments = [
            ['vendor_id' => 'V1', 'amount' => 100],
            ['vendor_id' => 'V2', 'amount' => 200],
        ];

        $result = $b2b->bulkPayment($payments);

        $this->assertArrayHasKey('batch_id', $result);
        $this->assertEquals(2, $result['total_payments']);
        $this->assertEquals(300.00, $result['total_amount']);
    }

    public function test_p2p_send_money()
    {
        $p2p = new P2PPayment();
        
        $response = $p2p->sendMoney([
            'amount' => 100.00,
            'recipient_id' => 'user_456',
        ]);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('send', $response->getData()['type']);
        $this->assertEquals(100.00, $response->getData()['amount']);
    }

    public function test_p2p_send_money_requires_amount()
    {
        $p2p = new P2PPayment();
        
        $response = $p2p->sendMoney(['recipient_id' => 'user_123']);
        $this->assertFalse($response->isSuccessful());
    }

    public function test_p2p_request_money()
    {
        $p2p = new P2PPayment();
        
        $response = $p2p->requestMoney([
            'amount' => 50.00,
            'sender_id' => 'user_789',
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
            'splits' => [
                ['recipient_id' => 'u1', 'amount' => 150],
                ['recipient_id' => 'u2', 'amount' => 150],
            ],
        ]);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('split', $response->getData()['type']);
        $this->assertCount(2, $response->getData()['splits']);
    }

    public function test_p2p_split_payment_invalid_amounts()
    {
        $p2p = new P2PPayment();
        
        $response = $p2p->splitPayment([
            'amount' => 300.00,
            'splits' => [
                ['recipient_id' => 'u1', 'amount' => 100],
                ['recipient_id' => 'u2', 'amount' => 100],
            ],
        ]);

        $this->assertFalse($response->isSuccessful());
    }

    public function test_p2p_group_payment()
    {
        $p2p = new P2PPayment();
        
        $response = $p2p->groupPayment([
            'total_amount' => 300.00,
            'participants' => ['u1', 'u2', 'u3'],
            'currency' => 'USD',
        ]);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals(100.00, $response->getData()['per_person']);
        $this->assertEquals(3, $response->getData()['participants']);
    }

    public function test_p2p_escrow()
    {
        $p2p = new P2PPayment();
        
        $response = $p2p->escrowPayment([
            'amount' => 500.00,
            'recipient_id' => 'seller_user',
            'conditions' => 'Delivery confirmation',
        ]);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('held', $response->getData()['status']);
    }

    public function test_p2p_release_escrow()
    {
        $p2p = new P2PPayment();
        
        $response = $p2p->releaseEscrow('ESCROW_123', []);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('released', $response->getData()['status']);
    }

    public function test_p2p_bank_transfer()
    {
        $p2p = new P2PPayment();
        
        $response = $p2p->bankTransfer([
            'amount' => 1000.00,
            'bank_account' => '1234567890',
            'bank_name' => 'Test Bank',
        ]);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('bank_transfer', $response->getData()['type']);
    }

    public function test_p2p_mobile_wallet()
    {
        $p2p = new P2PPayment();
        
        $response = $p2p->mobileWallet([
            'amount' => 50.00,
            'wallet_number' => '1234567890',
            'wallet_provider' => 'MPESA',
        ]);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('mobile_wallet', $response->getData()['type']);
    }

    public function test_p2p_international_transfer()
    {
        $p2p = new P2PPayment();
        
        $response = $p2p->internationalTransfer([
            'amount' => 1000.00,
            'recipient_id' => 'intl_user',
            'recipient_currency' => 'EUR',
        ]);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('international', $response->getData()['type']);
        $this->assertArrayHasKey('exchange_rate', $response->getData());
    }

    public function test_p2p_recurring_payment()
    {
        $p2p = new P2PPayment();
        
        $response = $p2p->recurringPayment([
            'amount' => 99.00,
            'recipient_id' => 'recurring_vendor',
            'frequency' => 'monthly',
        ]);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('recurring', $response->getData()['type']);
        $this->assertEquals('active', $response->getData()['status']);
    }

    public function test_p2p_refund()
    {
        $p2p = new P2PPayment();
        
        $response = $p2p->refundP2P('TX_123', 50.00);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('refunded', $response->getData()['status']);
        $this->assertEquals(50.00, $response->getData()['refund_amount']);
    }
}
