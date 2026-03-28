<?php

namespace ShamimStack\WwwPay\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ShamimStack\WwwPay\Gateways\Global\StripeGateway;
use ShamimStack\WwwPay\Gateways\Global\PayPalGateway;
use ShamimStack\WwwPay\Gateways\Bangladesh\BkashGateway;
use ShamimStack\WwwPay\Gateways\India\UpiGateway;
use ShamimStack\WwwPay\Gateways\Pakistan\JazzCashGateway;
use ShamimStack\WwwPay\Gateways\MiddleEast\MadaGateway;
use ShamimStack\WwwPay\Gateways\SouthAfrica\PayFastGateway;
use ShamimStack\WwwPay\Gateways\China\AlipayGateway;
use ShamimStack\WwwPay\Gateways\Crypto\BitcoinGateway;
use ShamimStack\WwwPay\Gateways\NorthAmerica\SquareGateway;
use ShamimStack\WwwPay\Gateways\LatinAmerica\MercadoPagoGateway;
use ShamimStack\WwwPay\Gateways\Europe\AdyenGateway;
use ShamimStack\WwwPay\Gateways\Africa\PaystackGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;

class GatewayTest extends TestCase
{
    public function test_stripe_gateway_returns_payment_response()
    {
        $gateway = new StripeGateway([
            'api_key' => 'test_key',
            'api_secret' => 'test_secret',
            'webhook_secret' => 'test_webhook_secret',
        ]);

        $response = $gateway->pay([
            'amount' => 100,
            'currency' => 'USD',
            'payment_method' => 'pm_card_visa',
        ]);

        $this->assertInstanceOf(PaymentResponse::class, $response);
    }

    public function test_stripe_gateway_has_correct_name()
    {
        $gateway = new StripeGateway([]);
        $this->assertEquals('stripe', $gateway->getName());
    }

    public function test_paypal_gateway_has_correct_name()
    {
        $gateway = new PayPalGateway([]);
        $this->assertEquals('paypal', $gateway->getName());
    }

    public function test_bkash_gateway_has_correct_name()
    {
        $gateway = new BkashGateway([]);
        $this->assertEquals('bkash', $gateway->getName());
    }

    public function test_upi_gateway_has_correct_name()
    {
        $gateway = new UpiGateway([]);
        $this->assertEquals('upi', $gateway->getName());
    }

    public function test_jazzcash_gateway_has_correct_name()
    {
        $gateway = new JazzCashGateway([]);
        $this->assertEquals('jazzcash', $gateway->getName());
    }

    public function test_mada_gateway_has_correct_name()
    {
        $gateway = new MadaGateway([]);
        $this->assertEquals('mada', $gateway->getName());
    }

    public function test_payfast_gateway_has_correct_name()
    {
        $gateway = new PayFastGateway([]);
        $this->assertEquals('payfast', $gateway->getName());
    }

    public function test_alipay_gateway_has_correct_name()
    {
        $gateway = new AlipayGateway([]);
        $this->assertEquals('alipay', $gateway->getName());
    }

    public function test_bitcoin_gateway_has_correct_name()
    {
        $gateway = new BitcoinGateway([]);
        $this->assertEquals('bitcoin', $gateway->getName());
    }

    public function test_square_gateway_has_correct_name()
    {
        $gateway = new SquareGateway([]);
        $this->assertEquals('square', $gateway->getName());
    }

    public function test_mercadopago_gateway_has_correct_name()
    {
        $gateway = new MercadoPagoGateway([]);
        $this->assertEquals('mercadopago', $gateway->getName());
    }

    public function test_adyen_gateway_has_correct_name()
    {
        $gateway = new AdyenGateway([]);
        $this->assertEquals('adyen', $gateway->getName());
    }

    public function test_paystack_gateway_has_correct_name()
    {
        $gateway = new PaystackGateway([]);
        $this->assertEquals('paystack', $gateway->getName());
    }

    public function test_all_gateways_implement_payment_gateway_interface()
    {
        $gateways = [
            new StripeGateway([]),
            new PayPalGateway([]),
            new BkashGateway([]),
            new UpiGateway([]),
            new JazzCashGateway([]),
            new MadaGateway([]),
            new PayFastGateway([]),
            new AlipayGateway([]),
            new BitcoinGateway([]),
            new SquareGateway([]),
            new MercadoPagoGateway([]),
            new AdyenGateway([]),
            new PaystackGateway([]),
        ];

        foreach ($gateways as $gateway) {
            $this->assertInstanceOf(
                \ShamimStack\WwwPay\Contracts\PaymentGateway::class,
                $gateway,
                get_class($gateway) . ' does not implement PaymentGateway interface'
            );
        }
    }

    public function test_all_gateways_have_required_methods()
    {
        $requiredMethods = ['pay', 'refund', 'cancel', 'subscribe', 'handleWebhook', 'getName'];

        $gateways = [
            new StripeGateway([]),
            new PayPalGateway([]),
            new BkashGateway([]),
            new UpiGateway([]),
            new JazzCashGateway([]),
            new MadaGateway([]),
            new PayFastGateway([]),
            new AlipayGateway([]),
            new BitcoinGateway([]),
            new SquareGateway([]),
            new MercadoPagoGateway([]),
            new AdyenGateway([]),
            new PaystackGateway([]),
        ];

        foreach ($gateways as $gateway) {
            foreach ($requiredMethods as $method) {
                $this->assertTrue(
                    method_exists($gateway, $method),
                    get_class($gateway) . ' missing method: ' . $method
                );
            }
        }
    }

    public function test_gateway_refund_returns_payment_response()
    {
        $gateway = new StripeGateway([]);
        $response = $gateway->refund('txn_123', 50.00);

        $this->assertInstanceOf(PaymentResponse::class, $response);
    }

    public function test_gateway_cancel_returns_payment_response()
    {
        $gateway = new StripeGateway([]);
        $response = $gateway->cancel('txn_123');

        $this->assertInstanceOf(PaymentResponse::class, $response);
    }

    public function test_gateway_has_subscribe_method(): void
    {
        $gateway = new StripeGateway([]);
        
        $this->assertTrue(method_exists($gateway, 'subscribe'));
    }
    
    public function test_gateway_subscribe_returns_subscription_instance()
    {
        $this->markTestSkipped('Subscription tests require Laravel application context with database connection.');
    }
}
