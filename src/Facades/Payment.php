<?php

namespace ShamimStack\WwwPay\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ShamimStack\WwwPay\PaymentManager
 */
class Payment extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'payment';
    }
}