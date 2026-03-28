<?php

namespace ShamimStack\WwwPay\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ShamimStack\WwwPay\Http\Middleware\VerifyPaymentSecurity;
use ShamimStack\WwwPay\Security\FraudDetection;

class VerifyPaymentSecurityMiddlewareTest extends TestCase
{
    public function test_middleware_class_exists(): void
    {
        $this->assertTrue(class_exists(VerifyPaymentSecurity::class));
    }

    public function test_fraud_detection_exists(): void
    {
        $this->assertTrue(class_exists(FraudDetection::class));
    }

    public function test_middleware_has_handle_method(): void
    {
        $reflection = new \ReflectionClass(VerifyPaymentSecurity::class);
        $this->assertTrue($reflection->hasMethod('handle'));
    }
}
