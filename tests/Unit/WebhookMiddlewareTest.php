<?php

namespace ShamimStack\WwwPay\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ShamimStack\WwwPay\Http\Middleware\VerifyWebhookSignature;

class VerifyWebhookSignatureMiddlewareTest extends TestCase
{
    public function test_middleware_class_exists(): void
    {
        $this->assertTrue(class_exists(VerifyWebhookSignature::class));
    }

    public function test_middleware_has_handle_method(): void
    {
        $reflection = new \ReflectionClass(VerifyWebhookSignature::class);
        $this->assertTrue($reflection->hasMethod('handle'));
    }

    public function test_middleware_has_stripe_verification(): void
    {
        $reflection = new \ReflectionClass(VerifyWebhookSignature::class);
        $this->assertTrue($reflection->hasMethod('verifyStripe'));
    }

    public function test_middleware_has_paystack_verification(): void
    {
        $reflection = new \ReflectionClass(VerifyWebhookSignature::class);
        $this->assertTrue($reflection->hasMethod('verifyPaystack'));
    }
}
