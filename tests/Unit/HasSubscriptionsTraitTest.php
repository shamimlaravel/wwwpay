<?php

namespace ShamimStack\WwwPay\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ShamimStack\WwwPay\Traits\HasSubscriptions;

class HasSubscriptionsTraitTest extends TestCase
{
    public function test_trait_can_be_used(): void
    {
        $this->assertTrue(trait_exists(HasSubscriptions::class));
    }
}
