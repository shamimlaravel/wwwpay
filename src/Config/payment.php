<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | This option controls the default payment gateway that will be used
    | when using the Payment facade without specifying a gateway.
    |
    */

    'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY', 'stripe'),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure each of the payment gateways used by the application.
    | Each gateway has its own set of required and optional parameters.
    |
    */

    'gateways' => [

        /*
        |--------------------------------------------------------------------------
        | Stripe Gateway
        |--------------------------------------------------------------------------
        */

        'stripe' => [
            'api_key' => env('STRIPE_KEY'),
            'api_secret' => env('STRIPE_SECRET'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        ],

        /*
        |--------------------------------------------------------------------------
        | PayPal Gateway
        |--------------------------------------------------------------------------
        */

        'paypal' => [
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'client_secret' => env('PAYPAL_CLIENT_SECRET'),
            'mode' => env('PAYPAL_MODE', 'sandbox'), // sandbox or live
        ],

        /*
        |--------------------------------------------------------------------------
        | bKash Gateway (Bangladesh)
        |--------------------------------------------------------------------------
        */

        'bkash' => [
            'app_key' => env('BKASH_APP_KEY'),
            'app_secret' => env('BKASH_APP_SECRET'),
            'username' => env('BKASH_USERNAME'),
            'password' => env('BKASH_PASSWORD'),
            'mode' => env('BKASH_MODE', 'sandbox'), // sandbox or live
        ],

        /*
        |--------------------------------------------------------------------------
        | Nagad Gateway (Bangladesh)
        |--------------------------------------------------------------------------
        */

        'nagad' => [
            'merchant_id' => env('NAGAD_MERCHANT_ID'),
            'merchant_password' => env('NAGAD_MERCHANT_PASSWORD'),
            'mode' => env('NAGAD_MODE', 'sandbox'), // sandbox or live
        ],

        /*
        |--------------------------------------------------------------------------
        | UPI Gateway (India)
        |--------------------------------------------------------------------------
        */

        'upi' => [
            'merchant_id' => env('UPI_MERCHANT_ID'),
            'merchant_key' => env('UPI_MERCHANT_KEY'),
            'mode' => env('UPI_MODE', 'sandbox'), // sandbox or live
        ],

        /*
        |--------------------------------------------------------------------------
        | PhonePe Gateway (India)
        |--------------------------------------------------------------------------
        */

        'phonepe' => [
            'merchant_id' => env('PHONEPE_MERCHANT_ID'),
            'salt_key' => env('PHONEPE_SALT_KEY'),
            'salt_index' => env('PHONEPE_SALT_INDEX', 1),
            'mode' => env('PHONEPE_MODE', 'sandbox'), // sandbox or live
        ],

        /*
        |--------------------------------------------------------------------------
        | Paytm Gateway (India)
        |--------------------------------------------------------------------------
        */

        'paytm' => [
            'merchant_id' => env('PAYTM_MERCHANT_ID'),
            'merchant_key' => env('PAYTM_MERCHANT_KEY'),
            'website' => env('PAYTM_WEBSITE', 'WEBSTAGING'),
            'mode' => env('PAYTM_MODE', 'sandbox'), // sandbox or live
        ],

        /*
        |--------------------------------------------------------------------------
        | JazzCash Gateway (Pakistan)
        |--------------------------------------------------------------------------
        */

        'jazzcash' => [
            'merchant_id' => env('JAZZCASH_MERCHANT_ID'),
            'password' => env('JAZZCASH_PASSWORD'),
            'integrity_salt' => env('JAZZCASH_INTEGRITY_SALT'),
            'mode' => env('JAZZCASH_MODE', 'sandbox'), // sandbox or live
        ],

        /*
        |--------------------------------------------------------------------------
        | Easypaisa Gateway (Pakistan)
        |--------------------------------------------------------------------------
        */

        'easypaisa' => [
            'merchant_id' => env('EASYPAISA_MERCHANT_ID'),
            'merchant_password' => env('EASYPAISA_MERCHANT_PASSWORD'),
            'mode' => env('EASYPAISA_MODE', 'sandbox'), // sandbox or live
        ],

        /*
        |--------------------------------------------------------------------------
        | PayTabs Gateway (Middle East)
        |--------------------------------------------------------------------------
        */

        'paytabs' => [
            'profile_id' => env('PAYTABS_PROFILE_ID'),
            'server_key' => env('PAYTABS_SERVER_KEY'),
            'mode' => env('PAYTABS_MODE', 'sandbox'), // sandbox or live
        ],

        /*
        |--------------------------------------------------------------------------
        | Telr Gateway (Middle East)
        |--------------------------------------------------------------------------
        */

        'telr' => [
            'merchant_id' => env('TELR_MERCHANT_ID'),
            'api_key' => env('TELR_API_KEY'),
            'mode' => env('TELR_MODE', 'test'), // test or live
        ],

        /*
        |--------------------------------------------------------------------------
        | Mada Gateway (Middle East - Saudi Arabia)
        |--------------------------------------------------------------------------
        */

        'mada' => [
            'merchant_id' => env('MADA_MERCHANT_ID'),
            'terminal_id' => env('MADA_TERMINAL_ID'),
            'secret_key' => env('MADA_SECRET_KEY'),
            'mode' => env('MADA_MODE', 'test'), // test or live
        ],

        /*
        |--------------------------------------------------------------------------
        | PayFast Gateway (South Africa)
        |--------------------------------------------------------------------------
        */

        'payfast' => [
            'merchant_id' => env('PAYFAST_MERCHANT_ID'),
            'merchant_key' => env('PAYFAST_MERCHANT_KEY'),
            'passphrase' => env('PAYFAST_PASSPHRASE'),
            'mode' => env('PAYFAST_MODE', 'test'), // test or live
        ],

        /*
        |--------------------------------------------------------------------------
        | SnapScan Gateway (South Africa)
        |--------------------------------------------------------------------------
        */

        'snapscan' => [
            'merchant_id' => env('SNAPSCAN_MERCHANT_ID'),
            'app_id' => env('SNAPSCAN_APP_ID'),
            'app_secret' => env('SNAPSCAN_APP_SECRET'),
            'mode' => env('SNAPSCAN_MODE', 'sandbox'), // sandbox or live
        ],

        /*
        |--------------------------------------------------------------------------
        | Alipay Gateway (China)
        |--------------------------------------------------------------------------
        */

        'alipay' => [
            'app_id' => env('ALIPAY_APP_ID'),
            'merchant_private_key' => env('ALIPAY_MERCHANT_PRIVATE_KEY'),
            'alipay_public_key' => env('ALIPAY_PUBLIC_KEY'),
            'mode' => env('ALIPAY_MODE', 'dev'), // dev or prod
        ],

        /*
        |--------------------------------------------------------------------------
        | WeChat Pay Gateway (China)
        |--------------------------------------------------------------------------
        */

        'wechat' => [
            'app_id' => env('WECHAT_APP_ID'),
            'mch_id' => env('WECHAT_MCH_ID'),
            'merchant_private_key' => env('WECHAT_MERCHANT_PRIVATE_KEY'),
            'wechat_public_key' => env('WECHAT_PUBLIC_KEY'),
            'mode' => env('WECHAT_MODE', 'dev'), // dev or prod
        ],

        /*
        |--------------------------------------------------------------------------
        | Bitcoin Gateway (Cryptocurrency)
        |--------------------------------------------------------------------------
        */

        'bitcoin' => [
            'api_key' => env('BITCOIN_API_KEY'),
            'api_secret' => env('BITCOIN_API_SECRET'),
            'mode' => env('BITCOIN_MODE', 'testnet'), // testnet or mainnet
        ],

        /*
        |--------------------------------------------------------------------------
        | Ethereum Gateway (Cryptocurrency)
        |--------------------------------------------------------------------------
        */

        'ethereum' => [
            'api_key' => env('ETHEREUM_API_KEY'),
            'api_secret' => env('ETHEREUM_API_SECRET'),
            'mode' => env('ETHEREUM_MODE', 'goerli'), // goerli, sepolia, mainnet
        ],

        /*
        |--------------------------------------------------------------------------
        | North America Gateways
        |--------------------------------------------------------------------------
        */

        'square' => [
            'access_token' => env('SQUARE_ACCESS_TOKEN'),
            'location_id' => env('SQUARE_LOCATION_ID'),
            'mode' => env('SQUARE_MODE', 'sandbox'), // sandbox or production
        ],

        'authorize' => [
            'api_login_id' => env('AUTHORIZE_API_LOGIN_ID'),
            'transaction_key' => env('AUTHORIZE_TRANSACTION_KEY'),
            'mode' => env('AUTHORIZE_MODE', 'test'), // test or live
        ],

        'moneris' => [
            'store_id' => env('MONERIS_STORE_ID'),
            'api_token' => env('MONERIS_API_TOKEN'),
            'mode' => env('MONERIS_MODE', 'test'), // test or live
        ],

        /*
        |--------------------------------------------------------------------------
        | Latin America Gateways
        |--------------------------------------------------------------------------
        */

        'mercadopago' => [
            'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
            'mode' => env('MERCADOPAGO_MODE', 'test'), // test or production
        ],

        'pagseguro' => [
            'email' => env('PAGSEGURO_EMAIL'),
            'token' => env('PAGSEGURO_TOKEN'),
            'mode' => env('PAGSEGURO_MODE', 'sandbox'), // sandbox or production
        ],

        /*
        |--------------------------------------------------------------------------
        | Europe Gateways
        |--------------------------------------------------------------------------
        */

        'klarna' => [
            'username' => env('KLARNA_USERNAME'),
            'password' => env('KLARNA_PASSWORD'),
            'mode' => env('KLARNA_MODE', 'test'), // test or live
        ],

        'adyen' => [
            'api_key' => env('ADYEN_API_KEY'),
            'merchant_account' => env('ADYEN_MERCHANT_ACCOUNT'),
            'mode' => env('ADYEN_MODE', 'test'), // test or live
        ],

        'ideal' => [
            'merchant_id' => env('IDEAL_MERCHANT_ID'),
            'subtoken' => env('IDEAL_SUBTOKEN'),
            'mode' => env('IDEAL_MODE', 'test'), // test or live
        ],

        'bancontact' => [
            'merchant_id' => env('BANCONTACT_MERCHANT_ID'),
            'api_key' => env('BANCONTACT_API_KEY'),
            'mode' => env('BANCONTACT_MODE', 'test'), // test or live
        ],

        /*
        |--------------------------------------------------------------------------
        | Asia Pacific Gateways
        |--------------------------------------------------------------------------
        */

        'paypay' => [
            'merchant_id' => env('PAYPAY_MERCHANT_ID'),
            'merchant_key' => env('PAYPAY_MERCHANT_KEY'),
            'mode' => env('PAYPAY_MODE', 'sandbox'), // sandbox or live
        ],

        'line_pay' => [
            'channel_id' => env('LINE_PAY_CHANNEL_ID'),
            'channel_secret' => env('LINE_PAY_CHANNEL_SECRET'),
            'mode' => env('LINE_PAY_MODE', 'sandbox'), // sandbox or live
        ],

        'grabpay' => [
            'merchant_id' => env('GRABPAY_MERCHANT_ID'),
            'merchant_key' => env('GRABPAY_MERCHANT_KEY'),
            'mode' => env('GRABPAY_MODE', 'sandbox'), // sandbox or live
        ],

        'polyguard' => [
            'merchant_id' => env('POLYGUARD_MERCHANT_ID'),
            'api_key' => env('POLYGUARD_API_KEY'),
            'mode' => env('POLYGUARD_MODE', 'sandbox'), // sandbox or live
        ],

        /*
        |--------------------------------------------------------------------------
        | Africa Gateways
        |--------------------------------------------------------------------------
        */

        'flutterwave' => [
            'public_key' => env('FLUTTERWAVE_PUBLIC_KEY'),
            'encryption_key' => env('FLUTTERWAVE_ENCRYPTION_KEY'),
            'mode' => env('FLUTTERWAVE_MODE', 'sandbox'), // sandbox or live
        ],

        'paystack' => [
            'public_key' => env('PAYSTACK_PUBLIC_KEY'),
            'secret_key' => env('PAYSTACK_SECRET_KEY'),
            'mode' => env('PAYSTACK_MODE', 'test'), // test or live
        ],

        'mpesa' => [
            'business_short_code' => env('MPESA_BUSINESS_SHORT_CODE'),
            'passkey' => env('MPESA_PASSKEY'),
            'initiator_name' => env('MPESA_INITIATOR_NAME'),
            'security_credential' => env('MPESA_SECURITY_CREDENTIAL'),
            'mode' => env('MPESA_MODE', 'sandbox'), // sandbox or live
        ],

        'fawry' => [
            'merchant_code' => env('FAWRY_MERCHANT_CODE'),
            'merchant_ref_num' => env('FAWRY_MERCHANT_REF_NUM'),
            'secure_key' => env('FAWRY_SECURE_KEY'),
            'mode' => env('FAWRY_MODE', 'sandbox'), // sandbox or live
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency Settings
    |--------------------------------------------------------------------------
    |
    | These settings control the currency handling for payments.
    |
    */

    'currency' => [

        /*
        |--------------------------------------------------------------------------
        | Base Currency
        |--------------------------------------------------------------------------
        |
        | The base currency used for all transactions in the application.
        |
        */

        'base' => env('PAYMENT_BASE_CURRENCY', 'USD'),

        /*
        |--------------------------------------------------------------------------
        | Allowed Currencies
        |--------------------------------------------------------------------------
        |
        | An array of currency codes that are allowed for transactions.
        | If empty, all currencies are allowed.
        |
        */

        'allowed' => env('PAYMENT_ALLOWED_CURRENCIES', [
            'USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY',
            'SEK', 'NZD', 'MXN', 'SGD', 'HKD', 'NOK', 'KRW', 'TRY',
            'RUB', 'INR', 'BRL', 'ZAR', 'BDT', 'PKR', 'NGN', 'KES',
            'EGP', 'GHS', 'UGX', 'TZS'
        ]),

        /*
        |--------------------------------------------------------------------------
        | Exchange Rate Provider
        |--------------------------------------------------------------------------
        |
        | The service used to fetch exchange rates for currency conversion.
        |
        */

        'provider' => env('PAYMENT_EXCHANGE_RATE_PROVIDER', 'exchangerate'),

        /*
        |--------------------------------------------------------------------------
        | Exchange Rate API Key
        |--------------------------------------------------------------------------
        |
        | API key for the exchange rate provider (if required).
        |
        */

        'api_key' => env('PAYMENT_EXCHANGE_RATE_API_KEY'),

    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    |
    | These settings control the webhook handling for payment gateways.
    |
    */

    'webhook' => [

        /*
        |--------------------------------------------------------------------------
        | Webhook Verification
        |--------------------------------------------------------------------------
        |
        | Whether to verify webhook signatures for security.
        |
        */

        'verify_signatures' => env('PAYMENT_WEBHOOK_VERIFY_SIGNATURES', true),

        /*
        |--------------------------------------------------------------------------
        | Webhook Logs
        |--------------------------------------------------------------------------
        |
        | Whether to log webhook requests and responses.
        |
        */

        'logs' => env('PAYMENT_WEBHOOK_LOGS', true),

    ],

    /*
    |--------------------------------------------------------------------------
    | Database Settings
    |--------------------------------------------------------------------------
    |
    | These settings control the database tables used by the package.
    |
    */

    'database' => [

        /*
        |--------------------------------------------------------------------------
        | Transaction Table
        |--------------------------------------------------------------------------
        |
        | The table used to store payment transactions.
        |
        */

        'transactions_table' => env('PAYMENT_TRANSACTIONS_TABLE', 'payment_transactions'),

        /*
        |--------------------------------------------------------------------------
        | Subscription Table
        |--------------------------------------------------------------------------
        |
        | The table used to store subscription information.
        |
        */

        'subscriptions_table' => env('PAYMENT_SUBSCRIPTIONS_TABLE', 'payment_subscriptions'),

        /*
        |--------------------------------------------------------------------------
        | Refund Table
        |--------------------------------------------------------------------------
        |
        | The table used to store refund information.
        |
        */

        'refunds_table' => env('PAYMENT_REFUNDS_TABLE', 'payment_refunds'),

    ],

];