<?php

namespace ShamimStack\WwwPay\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use ShamimStack\WwwPay\Providers\PaymentServiceProvider;
use ShamimStack\WwwPay\PaymentManager;
use ShamimStack\WwwPay\Models\Transaction;
use ShamimStack\WwwPay\Models\Subscription;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            PaymentServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Payment' => \ShamimStack\WwwPay\Facades\Payment::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('payment.default_gateway', 'stripe');
        $app['config']->set('payment.gateways.stripe', [
            'api_key' => 'test_key',
            'api_secret' => 'test_secret',
            'webhook_secret' => 'test_webhook_secret',
        ]);
        $app['config']->set('payment.gateways.paypal', [
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'mode' => 'sandbox',
        ]);
    }
}
