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