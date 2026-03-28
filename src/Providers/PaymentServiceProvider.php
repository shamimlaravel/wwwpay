<?php

namespace ShamimStack\WwwPay\Providers;

use Illuminate\Support\ServiceProvider;
use ShamimStack\WwwPay\Events\PaymentEvent;
use ShamimStack\WwwPay\Events\PaymentSuccessful;
use ShamimStack\WwwPay\Events\PaymentFailed;
use ShamimStack\WwwPay\Events\RefundProcessed;
use ShamimStack\WwwPay\Events\SubscriptionCreated;
use ShamimStack\WwwPay\Events\SubscriptionCancelled;
use ShamimStack\WwwPay\Events\WebhookReceived;
use ShamimStack\WwwPay\Gateways\Global\StripeGateway;
use ShamimStack\WwwPay\Gateways\Global\PayPalGateway;
use ShamimStack\WwwPay\Gateways\Bangladesh\BkashGateway;
use ShamimStack\WwwPay\Gateways\Bangladesh\NagadGateway;
use ShamimStack\WwwPay\Gateways\India\UpiGateway;
use ShamimStack\WwwPay\Gateways\India\PhonePeGateway;
use ShamimStack\WwwPay\Gateways\India\PaytmGateway;
use ShamimStack\WwwPay\Gateways\Pakistan\JazzCashGateway;
use ShamimStack\WwwPay\Gateways\Pakistan\EasypaisaGateway;
use ShamimStack\WwwPay\Gateways\MiddleEast\PayTabsGateway;
use ShamimStack\WwwPay\Gateways\MiddleEast\TelrGateway;
use ShamimStack\WwwPay\Gateways\MiddleEast\MadaGateway;
use ShamimStack\WwwPay\Gateways\SouthAfrica\PayFastGateway;
use ShamimStack\WwwPay\Gateways\SouthAfrica\SnapScanGateway;
use ShamimStack\WwwPay\Gateways\China\AlipayGateway;
use ShamimStack\WwwPay\Gateways\China\WeChatPayGateway;
use ShamimStack\WwwPay\Gateways\Crypto\BitcoinGateway;
use ShamimStack\WwwPay\Gateways\Crypto\EthereumGateway;
use ShamimStack\WwwPay\Gateways\NorthAmerica\SquareGateway;
use ShamimStack\WwwPay\Gateways\NorthAmerica\AuthorizeGateway;
use ShamimStack\WwwPay\Gateways\NorthAmerica\MonerisGateway;
use ShamimStack\WwwPay\Gateways\LatinAmerica\MercadoPagoGateway;
use ShamimStack\WwwPay\Gateways\LatinAmerica\PagSeguroGateway;
use ShamimStack\WwwPay\Gateways\Europe\KlarnaGateway;
use ShamimStack\WwwPay\Gateways\Europe\SEPAGateway;
use ShamimStack\WwwPay\Gateways\Europe\AdyenGateway;
use ShamimStack\WwwPay\Gateways\Europe\iDEALGateway;
use ShamimStack\WwwPay\Gateways\Europe\BancontactGateway;
use ShamimStack\WwwPay\Gateways\AsiaPacific\PayPayGateway;
use ShamimStack\WwwPay\Gateways\AsiaPacific\LinePayGateway;
use ShamimStack\WwwPay\Gateways\AsiaPacific\GrabPayGateway;
use ShamimStack\WwwPay\Gateways\Africa\FlutterwaveGateway;
use ShamimStack\WwwPay\Gateways\Africa\PaystackGateway;
use ShamimStack\WwwPay\Gateways\SouthAsia\AirtelMoneyGateway;
use ShamimStack\WwwPay\Gateways\SouthAsia\India\AmazonPayGateway;
use ShamimStack\WwwPay\Gateways\SouthAsia\India\FreeChargeGateway;
use ShamimStack\WwwPay\Gateways\SouthAsia\India\MobikwikGateway;
use ShamimStack\WwwPay\Gateways\SouthAsia\Pakistan\SimPayGateway;
use ShamimStack\WwwPay\Gateways\MiddleEast\Jordan\ArabBankPayGateway;
use ShamimStack\WwwPay\Gateways\MiddleEast\UAE\CheckoutGateway;
use ShamimStack\WwwPay\Gateways\MiddleEast\Saudi\HyperPayGateway;
use ShamimStack\WwwPay\Gateways\Africa\Nigeria\InterswitchGateway;
use ShamimStack\WwwPay\Gateways\Africa\Nigeria\PagaGateway;
use ShamimStack\WwwPay\Gateways\Africa\Nigeria\VoguePayGateway;
use ShamimStack\WwwPay\Gateways\Africa\Mobile\OrangeMoneyGateway;
use ShamimStack\WwwPay\Gateways\Africa\Mobile\MtnMobileMoneyGateway;
use ShamimStack\WwwPay\Gateways\Africa\Mobile\AirtelAfricaGateway;
use ShamimStack\WwwPay\Gateways\Africa\Egypt\MasaryGateway;
use ShamimStack\WwwPay\Gateways\Europe\Germany\GiropayGateway;
use ShamimStack\WwwPay\Gateways\Europe\Germany\SofortGateway;
use ShamimStack\WwwPay\Gateways\Europe\Poland\Przelewy24Gateway;
use ShamimStack\WwwPay\Gateways\Europe\TrustlyGateway;
use ShamimStack\WwwPay\Gateways\Europe\Portugal\MultibancoGateway;
use ShamimStack\WwwPay\Gateways\Europe\Austria\EPSGateway;
use ShamimStack\WwwPay\Gateways\AsiaPacific\Korea\KakaoPayGateway;
use ShamimStack\WwwPay\Gateways\AsiaPacific\Korea\NaverPayGateway;
use ShamimStack\WwwPay\Gateways\AsiaPacific\Korea\TossPayGateway;
use ShamimStack\WwwPay\Gateways\AsiaPacific\Korea\DPaysGateway;
use ShamimStack\WwwPay\Gateways\AsiaPacific\Japan\RakutenPayGateway;
use ShamimStack\WwwPay\Gateways\AsiaPacific\Japan\MerpayGateway;
use ShamimStack\WwwPay\Gateways\AsiaPacific\Thailand\SevenElevenGateway;
use ShamimStack\WwwPay\Gateways\Americas\BlueSnapGateway;
use ShamimStack\WwwPay\Gateways\Americas\ChargifyGateway;
use ShamimStack\WwwPay\Gateways\Americas\PayUGateway;
use ShamimStack\WwwPay\Gateways\Americas\EBANXGateway;
use ShamimStack\WwwPay\Gateways\Americas\dLocalGateway;
use ShamimStack\WwwPay\Gateways\Crypto\USDTGateway;
use ShamimStack\WwwPay\Gateways\Crypto\USDCGateway;
use ShamimStack\WwwPay\Gateways\Crypto\LitecoinGateway;
use ShamimStack\WwwPay\Gateways\Crypto\RippleGateway;
use ShamimStack\WwwPay\Gateways\Crypto\BinanceGateway;
use ShamimStack\WwwPay\Gateways\Africa\Mobile\MpesaGateway;
use ShamimStack\WwwPay\Gateways\MiddleEast\Egypt\FawryGateway;
use ShamimStack\WwwPay\Gateways\Europe\PayguardGateway;
use ShamimStack\WwwPay\Gateways\Bangladesh\RocketGateway;
use ShamimStack\WwwPay\Gateways\Bangladesh\UpayGateway;
use ShamimStack\WwwPay\Gateways\Bangladesh\ShurjoPayGateway;
use ShamimStack\WwwPay\Gateways\Bangladesh\SSLCommerzGateway;
use ShamimStack\WwwPay\Gateways\Bangladesh\AamarPayGateway;
use ShamimStack\WwwPay\Gateways\Bangladesh\PathaoGateway;
use ShamimStack\WwwPay\Gateways\Bangladesh\CashBabaGateway;
use ShamimStack\WwwPay\Gateways\Bangladesh\QPayGateway;
use ShamimStack\WwwPay\Gateways\Bangladesh\FastPayGateway;
use ShamimStack\WwwPay\Gateways\Bangladesh\BangoPayGateway;
use ShamimStack\WwwPay\Gateways\Bangladesh\FlexPayGateway;
use ShamimStack\WwwPay\Gateways\Bangladesh\OnePayGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Indonesia\GoPayGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Indonesia\OVOGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Indonesia\DANAGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Indonesia\LinkAjaGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Indonesia\MidtransGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Indonesia\XenditGateway;
use ShamimStack\WwwPay\Gateways\Global\ApplePayGateway;
use ShamimStack\WwwPay\Gateways\Global\GooglePayGateway;
use ShamimStack\WwwPay\Gateways\Global\SamsungPayGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Vietnam\MoMoGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Vietnam\ZaloPayGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Vietnam\VNPayGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Vietnam\ViettelPayGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Thailand\TrueMoneyGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Thailand\TwoC2PGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Thailand\OmiseGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Philippines\GCashGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Philippines\MayaGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Philippines\DragonpayGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Malaysia\TouchNGoGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Malaysia\BoostGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Malaysia\IPay88Gateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Singapore\PayNowGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Singapore\NETSGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Myanmar\WavePayGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Cambodia\WingGateway;
use ShamimStack\WwwPay\Gateways\LatinAmerica\Brazil\PIXGateway;
use ShamimStack\WwwPay\Gateways\LatinAmerica\Brazil\BoletoGateway;
use ShamimStack\WwwPay\Gateways\LatinAmerica\Brazil\PicPayGateway;
use ShamimStack\WwwPay\Gateways\LatinAmerica\Mexico\OXXOGateway;
use ShamimStack\WwwPay\Gateways\LatinAmerica\Mexico\SPEIGateway;
use ShamimStack\WwwPay\Gateways\LatinAmerica\Mexico\ConektaGateway;
use ShamimStack\WwwPay\Gateways\LatinAmerica\Argentina\MercadoPagoARGateway;
use ShamimStack\WwwPay\Gateways\LatinAmerica\Colombia\PSEGateway;
use ShamimStack\WwwPay\Gateways\LatinAmerica\Chile\WebPayGateway;
use ShamimStack\WwwPay\Gateways\LatinAmerica\Peru\CulqiGateway;
use ShamimStack\WwwPay\Gateways\MENA\UAE\TabbyGateway;
use ShamimStack\WwwPay\Gateways\MENA\UAE\TamaraGateway;
use ShamimStack\WwwPay\Gateways\MENA\SaudiArabia\STCPayGateway;
use ShamimStack\WwwPay\Gateways\Global\BNPL\AfterpayGateway;
use ShamimStack\WwwPay\Gateways\Global\BNPL\AffirmGateway;
use ShamimStack\WwwPay\Gateways\Global\WiseGateway;
use ShamimStack\WwwPay\Gateways\Global\PayoneerGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Indonesia\DokuGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Indonesia\OYGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Indonesia\CimbGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Vietnam\OnePayGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Vietnam\VimoGateway;
use ShamimStack\WwwPay\Gateways\SoutheastAsia\Thailand\RabbitGateway;
use ShamimStack\WwwPay\Gateways\Africa\YocoGateway;
use ShamimStack\WwwPay\Gateways\Africa\PayDunyaGateway;
use ShamimStack\WwwPay\Gateways\Africa\TouchPayGateway;
use ShamimStack\WwwPay\Gateways\Africa\WariGateway;
use ShamimStack\WwwPay\Gateways\Africa\WaveCIMAGateway;
use ShamimStack\WwwPay\Gateways\Africa\SafaricomMpesaGateway;
use ShamimStack\WwwPay\Gateways\MENA\Kuwait\KnetGateway;
use ShamimStack\WwwPay\Gateways\Europe\PaysafecardGateway;
use ShamimStack\WwwPay\Gateways\Europe\QiwiGateway;
use ShamimStack\WwwPay\Gateways\Europe\YooKassaGateway;
use ShamimStack\WwwPay\Gateways\Europe\SberbankGateway;
use ShamimStack\WwwPay\Gateways\Americas\CloverGateway;
use ShamimStack\WwwPay\Gateways\Americas\AdyenTestGateway;
use ShamimStack\WwwPay\Gateways\Americas\EbanxLocalGateway;
use ShamimStack\WwwPay\Gateways\Global\SkrillGateway;
use ShamimStack\WwwPay\Gateways\Global\NetellerGateway;
use ShamimStack\WwwPay\Gateways\Global\AstroPayGateway;
use ShamimStack\WwwPay\Gateways\Global\RapydGateway;
use ShamimStack\WwwPay\Gateways\Global\PayeerGateway;
use ShamimStack\WwwPay\Gateways\Global\PaxumGateway;
use ShamimStack\WwwPay\Gateways\Global\SticPayGateway;
use ShamimStack\WwwPay\Gateways\SouthAsia\JuspayGateway;
use ShamimStack\WwwPay\Gateways\SouthAsia\CashfreeGateway;
use ShamimStack\WwwPay\Gateways\SouthAsia\RazorpayGateway;
use ShamimStack\WwwPay\Gateways\SouthAsia\InstamojoGateway;
use ShamimStack\WwwPay\Gateways\SouthAsia\PayumoneyGateway;
use ShamimStack\WwwPay\Gateways\AsiaPacific\PayLahGateway;
use ShamimStack\WwwPay\Gateways\MiddleEast\PayFortGateway;
use ShamimStack\WwwPay\Gateways\AsiaPacific\Japan\PayPayJPGateway;
use ShamimStack\WwwPay\Gateways\AsiaPacific\Japan\StripeJPGateway;
use ShamimStack\WwwPay\Gateways\Africa\PesapalGateway;
use ShamimStack\WwwPay\Gateways\Africa\JengaGateway;
use ShamimStack\WwwPay\Gateways\Africa\FlutterwaveUGateway;
use ShamimStack\WwwPay\Gateways\Africa\SumsubGateway;
use ShamimStack\WwwPay\Gateways\MENA\QPayGateway;
use ShamimStack\WwwPay\Gateways\MENA\OmanNetGateway;
use ShamimStack\WwwPay\Gateways\MENA\BenefitGateway;
use ShamimStack\WwwPay\Gateways\AsiaPacific\HKPayGateway;
use ShamimStack\WwwPay\Gateways\AsiaPacific\EasyPayGateway;
use ShamimStack\WwwPay\Gateways\AsiaPacific\NewebPayGateway;
use ShamimStack\WwwPay\Gateways\AsiaPacific\CvsPayGateway;
use ShamimStack\WwwPay\Gateways\Europe\NordeaGateway;
use ShamimStack\WwwPay\Gateways\Europe\BarclayGateway;
use ShamimStack\WwwPay\Gateways\Europe\MonetaGateway;
use ShamimStack\WwwPay\Gateways\Europe\ClearhausGateway;
use ShamimStack\WwwPay\Gateways\LatinAmerica\MercadoPagoCOGateway;
use ShamimStack\WwwPay\Gateways\LatinAmerica\TodoPagoGateway;
use ShamimStack\WwwPay\Gateways\Americas\StripeCAGateway;
use ShamimStack\WwwPay\Gateways\Global\EpayGateway;
use ShamimStack\WwwPay\Gateways\Global\TwoCheckoutGateway;
use ShamimStack\WwwPay\Gateways\Global\PaymentwallGateway;
use ShamimStack\WwwPay\Gateways\Global\GumroadGateway;
use ShamimStack\WwwPay\Gateways\Global\PaddleGateway;
use ShamimStack\WwwPay\Gateways\Global\LemonSqueezyGateway;
use ShamimStack\WwwPay\Gateways\Global\BitPayGateway;
use ShamimStack\WwwPay\Gateways\Global\CoinBaseGateway;
use ShamimStack\WwwPay\Gateways\SouthAsia\BilldeskGateway;
use ShamimStack\WwwPay\Gateways\SouthAsia\AtomGateway;
use ShamimStack\WwwPay\Gateways\SouthAsia\PayziiGateway;
use ShamimStack\WwwPay\Gateways\SouthAsia\SslCommerzBDGateway;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    protected $commands = [
        \ShamimStack\WwwPay\Console\Commands\PaymentInstallCommand::class,
        \ShamimStack\WwwPay\Console\Commands\GatewayListCommand::class,
        \ShamimStack\WwwPay\Console\Commands\GatewayTestCommand::class,
        \ShamimStack\WwwPay\Console\Commands\WebhookSetupCommand::class,
        \ShamimStack\WwwPay\Console\Commands\DemoCommand::class,
    ];

    public function register()
    {
        $this->app->singleton('payment', function ($app) {
            return new \ShamimStack\WwwPay\PaymentManager($app);
        });

        // Register gateways
        $this->app->singleton('payment.stripe', function ($app) {
            return new StripeGateway($app['config']->get('payment.gateways.stripe'));
        });

        $this->app->singleton('payment.paypal', function ($app) {
            return new PayPalGateway($app['config']->get('payment.gateways.paypal'));
        });

        $this->app->singleton('payment.bkash', function ($app) {
            return new BkashGateway($app['config']->get('payment.gateways.bkash'));
        });

        $this->app->singleton('payment.nagad', function ($app) {
            return new NagadGateway($app['config']->get('payment.gateways.nagad'));
        });

        $this->app->singleton('payment.upi', function ($app) {
            return new UpiGateway($app['config']->get('payment.gateways.upi'));
        });

        $this->app->singleton('payment.phonepe', function ($app) {
            return new PhonePeGateway($app['config']->get('payment.gateways.phonepe'));
        });

        $this->app->singleton('payment.paytm', function ($app) {
            return new PaytmGateway($app['config']->get('payment.gateways.paytm'));
        });

        $this->app->singleton('payment.jazzcash', function ($app) {
            return new JazzCashGateway($app['config']->get('payment.gateways.jazzcash'));
        });

        $this->app->singleton('payment.easypaisa', function ($app) {
            return new EasypaisaGateway($app['config']->get('payment.gateways.easypaisa'));
        });

        $this->app->singleton('payment.paytabs', function ($app) {
            return new PayTabsGateway($app['config']->get('payment.gateways.paytabs'));
        });

        $this->app->singleton('payment.telr', function ($app) {
            return new TelrGateway($app['config']->get('payment.gateways.telr'));
        });

        $this->app->singleton('payment.mada', function ($app) {
            return new MadaGateway($app['config']->get('payment.gateways.mada'));
        });

        $this->app->singleton('payment.payfast', function ($app) {
            return new PayFastGateway($app['config']->get('payment.gateways.payfast'));
        });

        $this->app->singleton('payment.snapscan', function ($app) {
            return new SnapScanGateway($app['config']->get('payment.gateways.snapscan'));
        });

        $this->app->singleton('payment.alipay', function ($app) {
            return new AlipayGateway($app['config']->get('payment.gateways.alipay'));
        });

        $this->app->singleton('payment.wechat', function ($app) {
            return new WeChatPayGateway($app['config']->get('payment.gateways.wechat'));
        });

        $this->app->singleton('payment.bitcoin', function ($app) {
            return new BitcoinGateway($app['config']->get('payment.gateways.bitcoin'));
        });

        $this->app->singleton('payment.ethereum', function ($app) {
            return new EthereumGateway($app['config']->get('payment.gateways.ethereum'));
        });

        $this->app->singleton('payment.square', function ($app) {
            return new SquareGateway($app['config']->get('payment.gateways.square'));
        });

        $this->app->singleton('payment.authorize', function ($app) {
            return new AuthorizeGateway($app['config']->get('payment.gateways.authorize'));
        });

        $this->app->singleton('payment.moneris', function ($app) {
            return new MonerisGateway($app['config']->get('payment.gateways.moneris'));
        });

        $this->app->singleton('payment.mercadopago', function ($app) {
            return new MercadoPagoGateway($app['config']->get('payment.gateways.mercadopago'));
        });

        $this->app->singleton('payment.pagseguro', function ($app) {
            return new PagSeguroGateway($app['config']->get('payment.gateways.pagseguro'));
        });

        $this->app->singleton('payment.klarna', function ($app) {
            return new KlarnaGateway($app['config']->get('payment.gateways.klarna'));
        });

        $this->app->singleton('payment.sepa', function ($app) {
            return new SEPAGateway($app['config']->get('payment.gateways.sepa'));
        });

        $this->app->singleton('payment.adyen', function ($app) {
            return new AdyenGateway($app['config']->get('payment.gateways.adyen'));
        });

        $this->app->singleton('payment.ideal', function ($app) {
            return new iDEALGateway($app['config']->get('payment.gateways.ideal'));
        });

        $this->app->singleton('payment.bancontact', function ($app) {
            return new BancontactGateway($app['config']->get('payment.gateways.bancontact'));
        });

        $this->app->singleton('payment.paypay', function ($app) {
            return new PayPayGateway($app['config']->get('payment.gateways.paypay'));
        });

        $this->app->singleton('payment.linepay', function ($app) {
            return new LinePayGateway($app['config']->get('payment.gateways.line_pay'));
        });

        $this->app->singleton('payment.grabpay', function ($app) {
            return new GrabPayGateway($app['config']->get('payment.gateways.grabpay'));
        });

        $this->app->singleton('payment.flutterwave', function ($app) {
            return new FlutterwaveGateway($app['config']->get('payment.gateways.flutterwave'));
        });

        $this->app->singleton('payment.paystack', function ($app) {
            return new PaystackGateway($app['config']->get('payment.gateways.paystack'));
        });

        $this->app->singleton('payment.amazonpay', function ($app) {
            return new AmazonPayGateway($app['config']->get('payment.gateways.amazonpay'));
        });

        $this->app->singleton('payment.freecharge', function ($app) {
            return new FreeChargeGateway($app['config']->get('payment.gateways.freecharge'));
        });

        $this->app->singleton('payment.mobikwik', function ($app) {
            return new MobikwikGateway($app['config']->get('payment.gateways.mobikwik'));
        });

        $this->app->singleton('payment.airtelmoney', function ($app) {
            return new AirtelMoneyGateway($app['config']->get('payment.gateways.airtelmoney'));
        });

        $this->app->singleton('payment.simpay', function ($app) {
            return new SimPayGateway($app['config']->get('payment.gateways.simpay'));
        });

        $this->app->singleton('payment.arabbankpay', function ($app) {
            return new ArabBankPayGateway($app['config']->get('payment.gateways.arabbankpay'));
        });

        $this->app->singleton('payment.checkout', function ($app) {
            return new CheckoutGateway($app['config']->get('payment.gateways.checkout'));
        });

        $this->app->singleton('payment.hyperpay', function ($app) {
            return new HyperPayGateway($app['config']->get('payment.gateways.hyperpay'));
        });

        $this->app->singleton('payment.interswitch', function ($app) {
            return new InterswitchGateway($app['config']->get('payment.gateways.interswitch'));
        });

        $this->app->singleton('payment.paga', function ($app) {
            return new PagaGateway($app['config']->get('payment.gateways.paga'));
        });

        $this->app->singleton('payment.voguepay', function ($app) {
            return new VoguePayGateway($app['config']->get('payment.gateways.voguepay'));
        });

        $this->app->singleton('payment.orangemoney', function ($app) {
            return new OrangeMoneyGateway($app['config']->get('payment.gateways.orangemoney'));
        });

        $this->app->singleton('payment.mtnmobilemoney', function ($app) {
            return new MtnMobileMoneyGateway($app['config']->get('payment.gateways.mtnmobilemoney'));
        });

        $this->app->singleton('payment.airtelafrica', function ($app) {
            return new AirtelAfricaGateway($app['config']->get('payment.gateways.airtelafrica'));
        });

        $this->app->singleton('payment.masary', function ($app) {
            return new MasaryGateway($app['config']->get('payment.gateways.masary'));
        });

        $this->app->singleton('payment.giropay', function ($app) {
            return new GiropayGateway($app['config']->get('payment.gateways.giropay'));
        });

        $this->app->singleton('payment.sofort', function ($app) {
            return new SofortGateway($app['config']->get('payment.gateways.sofort'));
        });

        $this->app->singleton('payment.przelewy24', function ($app) {
            return new Przelewy24Gateway($app['config']->get('payment.gateways.przelewy24'));
        });

        $this->app->singleton('payment.trustly', function ($app) {
            return new TrustlyGateway($app['config']->get('payment.gateways.trustly'));
        });

        $this->app->singleton('payment.multibanco', function ($app) {
            return new MultibancoGateway($app['config']->get('payment.gateways.multibanco'));
        });

        $this->app->singleton('payment.eps', function ($app) {
            return new EPSGateway($app['config']->get('payment.gateways.eps'));
        });

        $this->app->singleton('payment.kakaopay', function ($app) {
            return new KakaoPayGateway($app['config']->get('payment.gateways.kakaopay'));
        });

        $this->app->singleton('payment.naverpay', function ($app) {
            return new NaverPayGateway($app['config']->get('payment.gateways.naverpay'));
        });

        $this->app->singleton('payment.tosspay', function ($app) {
            return new TossPayGateway($app['config']->get('payment.gateways.tosspay'));
        });

        $this->app->singleton('payment.dpay', function ($app) {
            return new DPaysGateway($app['config']->get('payment.gateways.dpay'));
        });

        $this->app->singleton('payment.rakutenpay', function ($app) {
            return new RakutenPayGateway($app['config']->get('payment.gateways.rakutenpay'));
        });

        $this->app->singleton('payment.merpay', function ($app) {
            return new MerpayGateway($app['config']->get('payment.gateways.merpay'));
        });

        $this->app->singleton('payment.seveneleven', function ($app) {
            return new SevenElevenGateway($app['config']->get('payment.gateways.seveneleven'));
        });

        $this->app->singleton('payment.bluesnap', function ($app) {
            return new BlueSnapGateway($app['config']->get('payment.gateways.bluesnap'));
        });

        $this->app->singleton('payment.chargify', function ($app) {
            return new ChargifyGateway($app['config']->get('payment.gateways.chargify'));
        });

        $this->app->singleton('payment.payu', function ($app) {
            return new PayUGateway($app['config']->get('payment.gateways.payu'));
        });

        $this->app->singleton('payment.ebanx', function ($app) {
            return new EBANXGateway($app['config']->get('payment.gateways.ebanx'));
        });

        $this->app->singleton('payment.dlocal', function ($app) {
            return new dLocalGateway($app['config']->get('payment.gateways.dlocal'));
        });

        $this->app->singleton('payment.usdt', function ($app) {
            return new USDTGateway($app['config']->get('payment.gateways.usdt'));
        });

        $this->app->singleton('payment.usdc', function ($app) {
            return new USDCGateway($app['config']->get('payment.gateways.usdc'));
        });

        $this->app->singleton('payment.litecoin', function ($app) {
            return new LitecoinGateway($app['config']->get('payment.gateways.litecoin'));
        });

        $this->app->singleton('payment.ripple', function ($app) {
            return new RippleGateway($app['config']->get('payment.gateways.ripple'));
        });

        $this->app->singleton('payment.binance', function ($app) {
            return new BinanceGateway($app['config']->get('payment.gateways.binance'));
        });

        $this->app->singleton('payment.mpesa', function ($app) {
            return new MpesaGateway($app['config']->get('payment.gateways.mpesa'));
        });

        $this->app->singleton('payment.fawry', function ($app) {
            return new FawryGateway($app['config']->get('payment.gateways.fawry'));
        });

        $this->app->singleton('payment.payguard', function ($app) {
            return new PayguardGateway($app['config']->get('payment.gateways.payguard'));
        });

        $this->app->singleton('payment.rocket', function ($app) {
            return new RocketGateway($app['config']->get('payment.gateways.rocket'));
        });

        $this->app->singleton('payment.upay', function ($app) {
            return new UpayGateway($app['config']->get('payment.gateways.upay'));
        });

        $this->app->singleton('payment.shurjopay', function ($app) {
            return new ShurjoPayGateway($app['config']->get('payment.gateways.shurjopay'));
        });

        $this->app->singleton('payment.sslcommerz', function ($app) {
            return new SSLCommerzGateway($app['config']->get('payment.gateways.sslcommerz'));
        });

        $this->app->singleton('payment.aamarpay', function ($app) {
            return new AamarPayGateway($app['config']->get('payment.gateways.aamarpay'));
        });

        $this->app->singleton('payment.pathao', function ($app) {
            return new PathaoGateway($app['config']->get('payment.gateways.pathao'));
        });

        $this->app->singleton('payment.cashbaba', function ($app) {
            return new CashBabaGateway($app['config']->get('payment.gateways.cashbaba'));
        });

        $this->app->singleton('payment.qpay', function ($app) {
            return new QPayGateway($app['config']->get('payment.gateways.qpay'));
        });

        $this->app->singleton('payment.fastpay', function ($app) {
            return new FastPayGateway($app['config']->get('payment.gateways.fastpay'));
        });

        $this->app->singleton('payment.bangopay', function ($app) {
            return new BangoPayGateway($app['config']->get('payment.gateways.bangopay'));
        });

        $this->app->singleton('payment.flexpay', function ($app) {
            return new FlexPayGateway($app['config']->get('payment.gateways.flexpay'));
        });

        $this->app->singleton('payment.onepay', function ($app) {
            return new OnePayGateway($app['config']->get('payment.gateways.onepay'));
        });

        $this->app->singleton('payment.gopay', function ($app) {
            return new GoPayGateway($app['config']->get('payment.gateways.gopay'));
        });

        $this->app->singleton('payment.ovo', function ($app) {
            return new OVOGateway($app['config']->get('payment.gateways.ovo'));
        });

        $this->app->singleton('payment.dana', function ($app) {
            return new DANAGateway($app['config']->get('payment.gateways.dana'));
        });

        $this->app->singleton('payment.linkaja', function ($app) {
            return new LinkAjaGateway($app['config']->get('payment.gateways.linkaja'));
        });

        $this->app->singleton('payment.midtrans', function ($app) {
            return new MidtransGateway($app['config']->get('payment.gateways.midtrans'));
        });

        $this->app->singleton('payment.xendit', function ($app) {
            return new XenditGateway($app['config']->get('payment.gateways.xendit'));
        });

        $this->app->singleton('payment.applepay', function ($app) {
            return new ApplePayGateway($app['config']->get('payment.gateways.applepay'));
        });

        $this->app->singleton('payment.googlepay', function ($app) {
            return new GooglePayGateway($app['config']->get('payment.gateways.googlepay'));
        });

        $this->app->singleton('payment.samsungpay', function ($app) {
            return new SamsungPayGateway($app['config']->get('payment.gateways.samsungpay'));
        });

        $this->app->singleton('payment.momo', function ($app) {
            return new MoMoGateway($app['config']->get('payment.gateways.momo'));
        });

        $this->app->singleton('payment.zalopay', function ($app) {
            return new ZaloPayGateway($app['config']->get('payment.gateways.zalopay'));
        });

        $this->app->singleton('payment.vnpay', function ($app) {
            return new VNPayGateway($app['config']->get('payment.gateways.vnpay'));
        });

        $this->app->singleton('payment.viettelpay', function ($app) {
            return new ViettelPayGateway($app['config']->get('payment.gateways.viettelpay'));
        });

        $this->app->singleton('payment.truemoney', function ($app) {
            return new TrueMoneyGateway($app['config']->get('payment.gateways.truemoney'));
        });

        $this->app->singleton('payment.2c2p', function ($app) {
            return new TwoC2PGateway($app['config']->get('payment.gateways.2c2p'));
        });

        $this->app->singleton('payment.omise', function ($app) {
            return new OmiseGateway($app['config']->get('payment.gateways.omise'));
        });

        $this->app->singleton('payment.gcash', function ($app) {
            return new GCashGateway($app['config']->get('payment.gateways.gcash'));
        });

        $this->app->singleton('payment.maya', function ($app) {
            return new MayaGateway($app['config']->get('payment.gateways.maya'));
        });

        $this->app->singleton('payment.dragonpay', function ($app) {
            return new DragonpayGateway($app['config']->get('payment.gateways.dragonpay'));
        });

        $this->app->singleton('payment.touchgo', function ($app) {
            return new TouchNGoGateway($app['config']->get('payment.gateways.touchgo'));
        });

        $this->app->singleton('payment.boost', function ($app) {
            return new BoostGateway($app['config']->get('payment.gateways.boost'));
        });

        $this->app->singleton('payment.ipay88', function ($app) {
            return new IPay88Gateway($app['config']->get('payment.gateways.ipay88'));
        });

        $this->app->singleton('payment.paynow', function ($app) {
            return new PayNowGateway($app['config']->get('payment.gateways.paynow'));
        });

        $this->app->singleton('payment.nets', function ($app) {
            return new NETSGateway($app['config']->get('payment.gateways.nets'));
        });

        $this->app->singleton('payment.wavepay', function ($app) {
            return new WavePayGateway($app['config']->get('payment.gateways.wavepay'));
        });

        $this->app->singleton('payment.wing', function ($app) {
            return new WingGateway($app['config']->get('payment.gateways.wing'));
        });

        $this->app->singleton('payment.pix', function ($app) {
            return new PIXGateway($app['config']->get('payment.gateways.pix'));
        });

        $this->app->singleton('payment.boleto', function ($app) {
            return new BoletoGateway($app['config']->get('payment.gateways.boleto'));
        });

        $this->app->singleton('payment.picpay', function ($app) {
            return new PicPayGateway($app['config']->get('payment.gateways.picpay'));
        });

        $this->app->singleton('payment.oxxo', function ($app) {
            return new OXXOGateway($app['config']->get('payment.gateways.oxxo'));
        });

        $this->app->singleton('payment.spei', function ($app) {
            return new SPEIGateway($app['config']->get('payment.gateways.spei'));
        });

        $this->app->singleton('payment.conekta', function ($app) {
            return new ConektaGateway($app['config']->get('payment.gateways.conekta'));
        });

        $this->app->singleton('payment.mercadopago_ar', function ($app) {
            return new MercadoPagoARGateway($app['config']->get('payment.gateways.mercadopago_ar'));
        });

        $this->app->singleton('payment.pse', function ($app) {
            return new PSEGateway($app['config']->get('payment.gateways.pse'));
        });

        $this->app->singleton('payment.webpay', function ($app) {
            return new WebPayGateway($app['config']->get('payment.gateways.webpay'));
        });

        $this->app->singleton('payment.culqi', function ($app) {
            return new CulqiGateway($app['config']->get('payment.gateways.culqi'));
        });

        $this->app->singleton('payment.tabby', function ($app) {
            return new TabbyGateway($app['config']->get('payment.gateways.tabby'));
        });

        $this->app->singleton('payment.tamara', function ($app) {
            return new TamaraGateway($app['config']->get('payment.gateways.tamara'));
        });

        $this->app->singleton('payment.stcpay', function ($app) {
            return new STCPayGateway($app['config']->get('payment.gateways.stcpay'));
        });

        $this->app->singleton('payment.afterpay', function ($app) {
            return new AfterpayGateway($app['config']->get('payment.gateways.afterpay'));
        });

        $this->app->singleton('payment.affirm', function ($app) {
            return new AffirmGateway($app['config']->get('payment.gateways.affirm'));
        });

        $this->app->singleton('payment.wise', function ($app) {
            return new WiseGateway($app['config']->get('payment.gateways.wise'));
        });

        $this->app->singleton('payment.payoneer', function ($app) {
            return new PayoneerGateway($app['config']->get('payment.gateways.payoneer'));
        });

        $this->app->singleton('payment.doku', function ($app) {
            return new DokuGateway($app['config']->get('payment.gateways.doku'));
        });

        $this->app->singleton('payment.oy', function ($app) {
            return new OYGateway($app['config']->get('payment.gateways.oy'));
        });

        $this->app->singleton('payment.cimb', function ($app) {
            return new CimbGateway($app['config']->get('payment.gateways.cimb'));
        });

        $this->app->singleton('payment.onepay', function ($app) {
            return new OnePayGateway($app['config']->get('payment.gateways.onepay'));
        });

        $this->app->singleton('payment.vimo', function ($app) {
            return new VimoGateway($app['config']->get('payment.gateways.vimo'));
        });

        $this->app->singleton('payment.rabbit', function ($app) {
            return new RabbitGateway($app['config']->get('payment.gateways.rabbit'));
        });

        $this->app->singleton('payment.yoco', function ($app) {
            return new YocoGateway($app['config']->get('payment.gateways.yoco'));
        });

        $this->app->singleton('payment.paydunya', function ($app) {
            return new PayDunyaGateway($app['config']->get('payment.gateways.paydunya'));
        });

        $this->app->singleton('payment.touchpay', function ($app) {
            return new TouchPayGateway($app['config']->get('payment.gateways.touchpay'));
        });

        $this->app->singleton('payment.wari', function ($app) {
            return new WariGateway($app['config']->get('payment.gateways.wari'));
        });

        $this->app->singleton('payment.wavecima', function ($app) {
            return new WaveCIMAGateway($app['config']->get('payment.gateways.wavecima'));
        });

        $this->app->singleton('payment.knet', function ($app) {
            return new KnetGateway($app['config']->get('payment.gateways.knet'));
        });

        $this->app->singleton('payment.paysafecard', function ($app) {
            return new PaysafecardGateway($app['config']->get('payment.gateways.paysafecard'));
        });

        $this->app->singleton('payment.qiwi', function ($app) {
            return new QiwiGateway($app['config']->get('payment.gateways.qiwi'));
        });

        $this->app->singleton('payment.yookassa', function ($app) {
            return new YooKassaGateway($app['config']->get('payment.gateways.yookassa'));
        });

        $this->app->singleton('payment.sberbank', function ($app) {
            return new SberbankGateway($app['config']->get('payment.gateways.sberbank'));
        });

        $this->app->singleton('payment.clover', function ($app) {
            return new CloverGateway($app['config']->get('payment.gateways.clover'));
        });

        $this->app->singleton('payment.adyen_test', function ($app) {
            return new AdyenTestGateway($app['config']->get('payment.gateways.adyen_test'));
        });

        $this->app->singleton('payment.ebanxlocal', function ($app) {
            return new EbanxLocalGateway($app['config']->get('payment.gateways.ebanxlocal'));
        });

        $this->app->singleton('payment.skrill', function ($app) {
            return new SkrillGateway($app['config']->get('payment.gateways.skrill'));
        });

        $this->app->singleton('payment.neteller', function ($app) {
            return new NetellerGateway($app['config']->get('payment.gateways.neteller'));
        });

        $this->app->singleton('payment.astropay', function ($app) {
            return new AstroPayGateway($app['config']->get('payment.gateways.astropay'));
        });

        $this->app->singleton('payment.rapyd', function ($app) {
            return new RapydGateway($app['config']->get('payment.gateways.rapyd'));
        });

        $this->app->singleton('payment.payeer', function ($app) {
            return new PayeerGateway($app['config']->get('payment.gateways.payeer'));
        });

        $this->app->singleton('payment.paxum', function ($app) {
            return new PaxumGateway($app['config']->get('payment.gateways.paxum'));
        });

        $this->app->singleton('payment.sticpay', function ($app) {
            return new SticPayGateway($app['config']->get('payment.gateways.sticpay'));
        });

        $this->app->singleton('payment.juspay', function ($app) {
            return new JuspayGateway($app['config']->get('payment.gateways.juspay'));
        });

        $this->app->singleton('payment.cashfree', function ($app) {
            return new CashfreeGateway($app['config']->get('payment.gateways.cashfree'));
        });

        $this->app->singleton('payment.razorpay', function ($app) {
            return new RazorpayGateway($app['config']->get('payment.gateways.razorpay'));
        });

        $this->app->singleton('payment.instamojo', function ($app) {
            return new InstamojoGateway($app['config']->get('payment.gateways.instamojo'));
        });

        $this->app->singleton('payment.payumoney', function ($app) {
            return new PayumoneyGateway($app['config']->get('payment.gateways.payumoney'));
        });

        $this->app->singleton('payment.paylah', function ($app) {
            return new PayLahGateway($app['config']->get('payment.gateways.paylah'));
        });

        $this->app->singleton('payment.payfort', function ($app) {
            return new PayFortGateway($app['config']->get('payment.gateways.payfort'));
        });

        $this->app->singleton('payment.paypayjp', function ($app) {
            return new PayPayJPGateway($app['config']->get('payment.gateways.paypayjp'));
        });

        $this->app->singleton('payment.stripejp', function ($app) {
            return new StripeJPGateway($app['config']->get('payment.gateways.stripejp'));
        });

        $this->app->singleton('payment.safaricommpesa', function ($app) {
            return new SafaricomMpesaGateway($app['config']->get('payment.gateways.safaricommpesa'));
        });

        $this->app->singleton('payment.pesapal', function ($app) {
            return new PesapalGateway($app['config']->get('payment.gateways.pesapal'));
        });

        $this->app->singleton('payment.jenga', function ($app) {
            return new JengaGateway($app['config']->get('payment.gateways.jenga'));
        });

        $this->app->singleton('payment.flutterwave_u', function ($app) {
            return new FlutterwaveUGateway($app['config']->get('payment.gateways.flutterwave_u'));
        });

        $this->app->singleton('payment.qpay', function ($app) {
            return new QPayGateway($app['config']->get('payment.gateways.qpay'));
        });

        $this->app->singleton('payment.omannet', function ($app) {
            return new OmanNetGateway($app['config']->get('payment.gateways.omannet'));
        });

        $this->app->singleton('payment.benefit', function ($app) {
            return new BenefitGateway($app['config']->get('payment.gateways.benefit'));
        });

        $this->app->singleton('payment.hkpay', function ($app) {
            return new HKPayGateway($app['config']->get('payment.gateways.hkpay'));
        });

        $this->app->singleton('payment.easypay', function ($app) {
            return new EasyPayGateway($app['config']->get('payment.gateways.easypay'));
        });

        $this->app->singleton('payment.newebpay', function ($app) {
            return new NewebPayGateway($app['config']->get('payment.gateways.newebpay'));
        });

        $this->app->singleton('payment.nordea', function ($app) {
            return new NordeaGateway($app['config']->get('payment.gateways.nordea'));
        });

        $this->app->singleton('payment.barclay', function ($app) {
            return new BarclayGateway($app['config']->get('payment.gateways.barclay'));
        });

        $this->app->singleton('payment.mercadopago_co', function ($app) {
            return new MercadoPagoCOGateway($app['config']->get('payment.gateways.mercadopago_co'));
        });

        $this->app->singleton('payment.todopago', function ($app) {
            return new TodoPagoGateway($app['config']->get('payment.gateways.todopago'));
        });

        $this->app->singleton('payment.stripe_ca', function ($app) {
            return new StripeCAGateway($app['config']->get('payment.gateways.stripe_ca'));
        });

        $this->app->singleton('payment.epay', function ($app) {
            return new EpayGateway($app['config']->get('payment.gateways.epay'));
        });

        $this->app->singleton('payment.2checkout', function ($app) {
            return new TwoCheckoutGateway($app['config']->get('payment.gateways.2checkout'));
        });

        $this->app->singleton('payment.paymentwall', function ($app) {
            return new PaymentwallGateway($app['config']->get('payment.gateways.paymentwall'));
        });

        $this->app->singleton('payment.gumroad', function ($app) {
            return new GumroadGateway($app['config']->get('payment.gateways.gumroad'));
        });

        $this->app->singleton('payment.paddle', function ($app) {
            return new PaddleGateway($app['config']->get('payment.gateways.paddle'));
        });

        $this->app->singleton('payment.lemonsqueezy', function ($app) {
            return new LemonSqueezyGateway($app['config']->get('payment.gateways.lemonsqueezy'));
        });

        $this->app->singleton('payment.billdesk', function ($app) {
            return new BilldeskGateway($app['config']->get('payment.gateways.billdesk'));
        });

        $this->app->singleton('payment.atom', function ($app) {
            return new AtomGateway($app['config']->get('payment.gateways.atom'));
        });

        $this->app->singleton('payment.payzii', function ($app) {
            return new PayziiGateway($app['config']->get('payment.gateways.payzii'));
        });

        $this->app->singleton('payment.sslcommerz_bd', function ($app) {
            return new SslCommerzBDGateway($app['config']->get('payment.gateways.sslcommerz_bd'));
        });

        $this->app->singleton('payment.sumsub', function ($app) {
            return new SumsubGateway($app['config']->get('payment.gateways.sumsub'));
        });

        $this->app->singleton('payment.moneta', function ($app) {
            return new MonetaGateway($app['config']->get('payment.gateways.moneta'));
        });

        $this->app->singleton('payment.bitpay', function ($app) {
            return new BitPayGateway($app['config']->get('payment.gateways.bitpay'));
        });

        $this->app->singleton('payment.coinbase', function ($app) {
            return new CoinBaseGateway($app['config']->get('payment.gateways.coinbase'));
        });

        $this->app->singleton('payment.clearhaus', function ($app) {
            return new ClearhausGateway($app['config']->get('payment.gateways.clearhaus'));
        });

        $this->app->singleton('payment.cvspay', function ($app) {
            return new CvsPayGateway($app['config']->get('payment.gateways.cvspay'));
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Register event listeners
        $this->registerEvents();

        // Publish config files
        $this->publishes([
            __DIR__.'/../Config/payment.php' => config_path('payment.php'),
        ], 'payment-config');

        // Publish migrations
        $this->publishes([
            dirname(__DIR__).'/../database/migrations/' => database_path('migrations'),
        ], 'payment-migrations');

        // Publish views (if any)
        $this->publishes([
            __DIR__.'/../Resources/views' => resource_path('views/vendor/payment'),
        ], 'payment-views');
    }

    /**
     * Register payment events.
     *
     * @return void
     */
    protected function registerEvents(): void
    {
        $events = [
            PaymentSuccessful::class,
            PaymentFailed::class,
            RefundProcessed::class,
            SubscriptionCreated::class,
            SubscriptionCancelled::class,
            WebhookReceived::class,
        ];

        foreach ($events as $event) {
            $this->app['events']->listen($event, function ($e) {
                // Default listeners can be registered here
                // Users can override in their EventServiceProvider
            });
        }
    }
}