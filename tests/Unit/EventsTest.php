<?php

namespace ShamimStack\WwwPay\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ShamimStack\WwwPay\Events\PaymentEvent;
use ShamimStack\WwwPay\Events\PaymentSuccessful;
use ShamimStack\WwwPay\Events\PaymentFailed;
use ShamimStack\WwwPay\Events\RefundProcessed;
use ShamimStack\WwwPay\Events\SubscriptionCreated;
use ShamimStack\WwwPay\Events\SubscriptionCancelled;
use ShamimStack\WwwPay\Events\WebhookReceived;

class EventsTest extends TestCase
{
    public function test_payment_event_exists(): void
    {
        $this->assertTrue(class_exists(PaymentEvent::class));
    }

    public function test_payment_successful_event_exists(): void
    {
        $this->assertTrue(class_exists(PaymentSuccessful::class));
    }

    public function test_payment_failed_event_exists(): void
    {
        $this->assertTrue(class_exists(PaymentFailed::class));
    }

    public function test_refund_processed_event_exists(): void
    {
        $this->assertTrue(class_exists(RefundProcessed::class));
    }

    public function test_subscription_created_event_exists(): void
    {
        $this->assertTrue(class_exists(SubscriptionCreated::class));
    }

    public function test_subscription_cancelled_event_exists(): void
    {
        $this->assertTrue(class_exists(SubscriptionCancelled::class));
    }

    public function test_webhook_received_event_exists(): void
    {
        $this->assertTrue(class_exists(WebhookReceived::class));
    }

    public function test_payment_successful_event_has_correct_properties(): void
    {
        $event = new PaymentSuccessful('stripe', 'txn_123', 100.00, 'USD');
        
        $this->assertEquals('stripe', $event->gateway);
        $this->assertEquals('txn_123', $event->transactionId);
        $this->assertEquals(100.00, $event->amount);
        $this->assertEquals('USD', $event->currency);
        $this->assertEquals('successful', $event->status);
    }

    public function test_payment_failed_event_has_error_info(): void
    {
        $event = new PaymentFailed('stripe', 'txn_123', 100.00, 'USD', 'ERR001', 'Card declined');
        
        $this->assertEquals('failed', $event->status);
        $this->assertEquals('ERR001', $event->errorCode);
        $this->assertEquals('Card declined', $event->errorMessage);
    }

    public function test_events_have_broadcast_on(): void
    {
        $event = new PaymentSuccessful('stripe', 'txn_123', 100.00, 'USD');
        $channels = $event->broadcastOn();
        
        $this->assertIsArray($channels);
    }

    public function test_events_have_get_tags(): void
    {
        $event = new PaymentSuccessful('stripe', 'txn_123', 100.00, 'USD');
        $tags = $event->getTags();
        
        $this->assertIsArray($tags);
        $this->assertContains('gateway:stripe', $tags);
        $this->assertContains('currency:USD', $tags);
    }
}
