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