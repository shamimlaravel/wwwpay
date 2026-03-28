<?php

namespace ShamimStack\WwwPay\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ShamimStack\WwwPay\Traits\HasPayments;
use ShamimStack\WwwPay\Facades\Payment;

class HasPaymentsTraitTest extends TestCase
{
    public function test_trait_can_be_used(): void
    {
        $this->assertTrue(trait_exists(HasPayments::class));
    }

    public function test_payment_facade_is_accessible(): void
    {
        $this->assertTrue(class_exists(Payment::class));
    }
}
