<?php

namespace ShamimStack\AllInOnePayment\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ShamimStack\AllInOnePayment\PaymentManager
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