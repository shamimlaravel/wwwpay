<?php

namespace ShamimStack\WwwPay\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ShamimStack\WwwPay\Traits\HandlesCurrency;
use ShamimStack\WwwPay\Helpers\CurrencyConverter;

class HandlesCurrencyTraitTest extends TestCase
{
    public function test_trait_can_be_used(): void
    {
        $this->assertTrue(trait_exists(HandlesCurrency::class));
    }

    public function test_currency_converter_class_exists(): void
    {
        $this->assertTrue(class_exists(CurrencyConverter::class));
    }
}
