<?php

namespace ShamimStack\WwwPay\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ShamimStack\WwwPay\Gateways\SouthAfrica\SnapScanGateway;
use ShamimStack\WwwPay\Gateways\China\WeChatPayGateway;
use ShamimStack\WwwPay\Gateways\Crypto\EthereumGateway;
use ShamimStack\WwwPay\Gateways\NorthAmerica\AuthorizeGateway;
use ShamimStack\WwwPay\Gateways\NorthAmerica\MonerisGateway;
use ShamimStack\WwwPay\Gateways\LatinAmerica\PagSeguroGateway;
use ShamimStack\WwwPay\Gateways\Europe\KlarnaGateway;
use ShamimStack\WwwPay\Gateways\Europe\SEPAGateway;
use ShamimStack\WwwPay\Gateways\Europe\iDEALGateway;
use ShamimStack\WwwPay\Gateways\Europe\BancontactGateway;
use ShamimStack\WwwPay\Gateways\AsiaPacific\PayPayGateway;
use ShamimStack\WwwPay\Gateways\AsiaPacific\LinePayGateway;
use ShamimStack\WwwPay\Gateways\AsiaPacific\GrabPayGateway;
use ShamimStack\WwwPay\Gateways\Africa\FlutterwaveGateway;
use ShamimStack\WwwPay\Gateways\India\PhonePeGateway;
use ShamimStack\WwwPay\Gateways\India\PaytmGateway;
use ShamimStack\WwwPay\Gateways\Bangladesh\NagadGateway;
use ShamimStack\WwwPay\Gateways\Pakistan\EasypaisaGateway;
use ShamimStack\WwwPay\Gateways\MiddleEast\PayTabsGateway;
use ShamimStack\WwwPay\Gateways\MiddleEast\TelrGateway;

class AllGatewaysTest extends TestCase
{
    private function assertGatewayInterface($gateway)
    {
        $this->assertInstanceOf(\ShamimStack\WwwPay\Contracts\PaymentGateway::class, $gateway);
    }

    private function assertGatewayMethods($gateway)
    {
        $methods = ['pay', 'refund', 'cancel', 'subscribe', 'handleWebhook', 'getName'];
        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists($gateway, $method),
                get_class($gateway) . " missing method: {$method}"
            );
        }
    }

    public function test_snapscan_gateway()
    {
        $gateway = new SnapScanGateway([]);
        $this->assertGatewayInterface($gateway);
        $this->assertGatewayMethods($gateway);
        $this->assertEquals('snapscan', $gateway->getName());
    }

    public function test_wechat_pay_gateway()
    {
        $gateway = new WeChatPayGateway([]);
        $this->assertGatewayInterface($gateway);
        $this->assertGatewayMethods($gateway);
        $this->assertEquals('wechat', $gateway->getName());
    }

    public function test_ethereum_gateway()
    {
        $gateway = new EthereumGateway([]);
        $this->assertGatewayInterface($gateway);
        $this->assertGatewayMethods($gateway);
        $this->assertEquals('ethereum', $gateway->getName());
    }

    public function test_authorize_gateway()
    {
        $gateway = new AuthorizeGateway([]);
        $this->assertGatewayInterface($gateway);
        $this->assertGatewayMethods($gateway);
        $this->assertEquals('authorize', $gateway->getName());
    }

    public function test_moneris_gateway()
    {
        $gateway = new MonerisGateway([]);
        $this->assertGatewayInterface($gateway);
        $this->assertGatewayMethods($gateway);
        $this->assertEquals('moneris', $gateway->getName());
    }

    public function test_pagseguro_gateway()
    {
        $gateway = new PagSeguroGateway([]);
        $this->assertGatewayInterface($gateway);
        $this->assertGatewayMethods($gateway);
        $this->assertEquals('pagseguro', $gateway->getName());
    }

    public function test_klarna_gateway()
    {
        $gateway = new KlarnaGateway([]);
        $this->assertGatewayInterface($gateway);
        $this->assertGatewayMethods($gateway);
        $this->assertEquals('klarna', $gateway->getName());
    }

    public function test_sepa_gateway()
    {
        $gateway = new SEPAGateway([]);
        $this->assertGatewayInterface($gateway);
        $this->assertGatewayMethods($gateway);
        $this->assertEquals('sepa', $gateway->getName());
    }

    public function test_ideal_gateway()
    {
        $gateway = new iDEALGateway([]);
        $this->assertGatewayInterface($gateway);
        $this->assertGatewayMethods($gateway);
        $this->assertEquals('ideal', $gateway->getName());
    }

    public function test_bancontact_gateway()
    {
        $gateway = new BancontactGateway([]);
        $this->assertGatewayInterface($gateway);
        $this->assertGatewayMethods($gateway);
        $this->assertEquals('bancontact', $gateway->getName());
    }

    public function test_paypay_gateway()
    {
        $gateway = new PayPayGateway([]);
        $this->assertGatewayInterface($gateway);
        $this->assertGatewayMethods($gateway);
        $this->assertEquals('paypay', $gateway->getName());
    }

    public function test_line_pay_gateway()
    {
        $gateway = new LinePayGateway([]);
        $this->assertGatewayInterface($gateway);
        $this->assertGatewayMethods($gateway);
        $this->assertEquals('linepay', $gateway->getName());
    }

    public function test_grab_pay_gateway()
    {
        $gateway = new GrabPayGateway([]);
        $this->assertGatewayInterface($gateway);
        $this->assertGatewayMethods($gateway);
        $this->assertEquals('grabpay', $gateway->getName());
    }

    public function test_flutterwave_gateway()
    {
        $gateway = new FlutterwaveGateway([]);
        $this->assertGatewayInterface($gateway);
        $this->assertGatewayMethods($gateway);
        $this->assertEquals('flutterwave', $gateway->getName());
    }

    public function test_phonepe_gateway()
    {
        $gateway = new PhonePeGateway([]);
        $this->assertGatewayInterface($gateway);
        $this->assertGatewayMethods($gateway);
        $this->assertEquals('phonepe', $gateway->getName());
    }

    public function test_paytm_gateway()
    {
        $gateway = new PaytmGateway([]);
        $this->assertGatewayInterface($gateway);
        $this->assertGatewayMethods($gateway);
        $this->assertEquals('paytm', $gateway->getName());
    }

    public function test_nagad_gateway()
    {
        $gateway = new NagadGateway([]);
        $this->assertGatewayInterface($gateway);
        $this->assertGatewayMethods($gateway);
        $this->assertEquals('nagad', $gateway->getName());
    }

    public function test_easypaisa_gateway()
    {
        $gateway = new EasypaisaGateway([]);
        $this->assertGatewayInterface($gateway);
        $this->assertGatewayMethods($gateway);
        $this->assertEquals('easypaisa', $gateway->getName());
    }

    public function test_paytabs_gateway()
    {
        $gateway = new PayTabsGateway([]);
        $this->assertGatewayInterface($gateway);
        $this->assertGatewayMethods($gateway);
        $this->assertEquals('paytabs', $gateway->getName());
    }

    public function test_telr_gateway()
    {
        $gateway = new TelrGateway([]);
        $this->assertGatewayInterface($gateway);
        $this->assertGatewayMethods($gateway);
        $this->assertEquals('telr', $gateway->getName());
    }

    public function test_all_gateways_have_subscribe_method(): void
    {
        $gateways = [
            new StripeGateway([]),
            new PayPalGateway([]),
            new BkashGateway([]),
            new NagadGateway([]),
            new UpiGateway([]),
            new PhonePeGateway([]),
            new PaytmGateway([]),
            new JazzCashGateway([]),
            new EasypaisaGateway([]),
            new PayTabsGateway([]),
            new TelrGateway([]),
            new MadaGateway([]),
            new PayFastGateway([]),
            new SnapScanGateway([]),
            new AlipayGateway([]),
            new WeChatPayGateway([]),
            new BitcoinGateway([]),
            new EthereumGateway([]),
            new SquareGateway([]),
            new AuthorizeGateway([]),
            new MonerisGateway([]),
            new MercadoPagoGateway([]),
            new PagSeguroGateway([]),
            new KlarnaGateway([]),
            new SEPAGateway([]),
            new AdyenGateway([]),
            new iDEALGateway([]),
            new BancontactGateway([]),
            new PayPayGateway([]),
            new LinePayGateway([]),
            new GrabPayGateway([]),
            new FlutterwaveGateway([]),
            new PaystackGateway([]),
        ];

        foreach ($gateways as $gateway) {
            $this->assertTrue(
                method_exists($gateway, 'subscribe'),
                get_class($gateway) . ' should have subscribe() method'
            );
        }
    }

    public function test_all_gateways_return_subscription_instance()
    {
        $this->markTestSkipped('Subscription tests require Laravel application context with database connection.');
    }

    public function test_all_gateways_return_valid_payment_response()
    {
        $gateways = [
            new SnapScanGateway([]),
            new WeChatPayGateway([]),
            new MonerisGateway([]),
            new PagSeguroGateway([]),
            new FlutterwaveGateway([]),
        ];

        foreach ($gateways as $gateway) {
            $response = $gateway->pay([
                'amount' => 100,
                'description' => 'Test payment',
            ]);

            $this->assertInstanceOf(
                \ShamimStack\WwwPay\Contracts\PaymentResponse::class,
                $response,
                get_class($gateway) . ' pay() should return PaymentResponse'
            );
        }
    }
}
