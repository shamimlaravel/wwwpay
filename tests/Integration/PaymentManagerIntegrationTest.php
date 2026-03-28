<?php

namespace ShamimStack\AllInOnePayment\Tests\Integration;

use ShamimStack\AllInOnePayment\Tests\TestCase;
use ShamimStack\AllInOnePayment\PaymentManager;
use ShamimStack\AllInOnePayment\Models\Transaction;
use ShamimStack\AllInOnePayment\Models\Subscription;

class PaymentManagerIntegrationTest extends TestCase
{
    public function test_payment_manager_can_be_resolved()
    {
        $manager = $this->app->make(PaymentManager::class);
        $this->assertInstanceOf(PaymentManager::class, $manager);
    }

    public function test_can_get_gateway_instance()
    {
        $manager = $this->app->make(PaymentManager::class);
        
        $gateway = $manager->gateway('stripe');
        $this->assertInstanceOf(\ShamimStack\AllInOnePayment\Contracts\PaymentGateway::class, $gateway);
        $this->assertEquals('stripe', $gateway->getName());
    }

    public function test_throws_exception_for_unknown_gateway()
    {
        $this->expectException(\ShamimStack\AllInOnePayment\Exceptions\InvalidConfigurationException::class);
        
        $manager = $this->app->make(PaymentManager::class);
        $manager->gateway('nonexistent');
    }

    public function test_can_access_all_registered_gateways()
    {
        $gateways = [
            'stripe', 'paypal', 'bkash', 'nagad', 'upi', 'phonepe', 'paytm',
            'jazzcash', 'easypaisa', 'paytabs', 'telr', 'mada', 'payfast',
            'snapscan', 'alipay', 'wechat', 'bitcoin', 'ethereum', 'square',
            'authorize', 'moneris', 'mercadopago', 'pagseguro', 'klarna',
            'sepa', 'adyen', 'ideal', 'bancontact', 'paypay', 'linepay',
            'grabpay', 'flutterwave', 'paystack'
        ];

        $manager = $this->app->make(PaymentManager::class);

        foreach ($gateways as $gatewayName) {
            try {
                $gateway = $manager->gateway($gatewayName);
                $this->assertEquals($gatewayName, $gateway->getName(), "Gateway {$gatewayName} name mismatch");
            } catch (\Exception $e) {
                $this->fail("Failed to load gateway {$gatewayName}: " . $e->getMessage());
            }
        }

        $this->assertCount(35, $gateways);
    }
}
