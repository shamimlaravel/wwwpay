<?php

namespace ShamimStack\AllInOnePayment\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ShamimStack\AllInOnePayment\Gateways\SouthAfrica\SnapScanGateway;
use ShamimStack\AllInOnePayment\Gateways\China\WeChatPayGateway;
use ShamimStack\AllInOnePayment\Gateways\Crypto\EthereumGateway;
use ShamimStack\AllInOnePayment\Gateways\NorthAmerica\AuthorizeGateway;
use ShamimStack\AllInOnePayment\Gateways\NorthAmerica\MonerisGateway;
use ShamimStack\AllInOnePayment\Gateways\LatinAmerica\PagSeguroGateway;
use ShamimStack\AllInOnePayment\Gateways\Europe\KlarnaGateway;
use ShamimStack\AllInOnePayment\Gateways\Europe\SEPAGateway;
use ShamimStack\AllInOnePayment\Gateways\Europe\iDEALGateway;
use ShamimStack\AllInOnePayment\Gateways\Europe\BancontactGateway;
use ShamimStack\AllInOnePayment\Gateways\AsiaPacific\PayPayGateway;
use ShamimStack\AllInOnePayment\Gateways\AsiaPacific\LinePayGateway;
use ShamimStack\AllInOnePayment\Gateways\AsiaPacific\GrabPayGateway;
use ShamimStack\AllInOnePayment\Gateways\Africa\FlutterwaveGateway;
use ShamimStack\AllInOnePayment\Gateways\India\PhonePeGateway;
use ShamimStack\AllInOnePayment\Gateways\India\PaytmGateway;
use ShamimStack\AllInOnePayment\Gateways\Bangladesh\NagadGateway;
use ShamimStack\AllInOnePayment\Gateways\Pakistan\EasypaisaGateway;
use ShamimStack\AllInOnePayment\Gateways\MiddleEast\PayTabsGateway;
use ShamimStack\AllInOnePayment\Gateways\MiddleEast\TelrGateway;

class AllGatewaysTest extends TestCase
{
    private function assertGatewayInterface($gateway)
    {
        $this->assertInstanceOf(\ShamimStack\AllInOnePayment\Contracts\PaymentGateway::class, $gateway);
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

    public function test_all_gateways_return_subscription_instance()
    {
        $this->markTestSkipped('Subscription tests require Laravel application context. Use Integration tests instead.');
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
                \ShamimStack\AllInOnePayment\Contracts\PaymentResponse::class,
                $response,
                get_class($gateway) . ' pay() should return PaymentResponse'
            );
        }
    }
}
