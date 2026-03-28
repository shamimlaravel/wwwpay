<?php

namespace ShamimStack\WwwPay\Console\Installers;

class GatewaySelector
{
    public const REGIONS = [
        'global' => [
            'name' => 'Global Gateways',
            'description' => 'Stripe, PayPal - Worldwide accepted',
            'gateways' => ['stripe', 'paypal'],
        ],
        'south_asia' => [
            'name' => 'South Asia',
            'description' => 'bKash, Nagad, UPI, PhonePe, Paytm, JazzCash, Easypaisa',
            'gateways' => ['bkash', 'nagad', 'upi', 'phonepe', 'paytm', 'jazzcash', 'easypaisa'],
        ],
        'middle_east' => [
            'name' => 'Middle East',
            'description' => 'PayTabs, Telr, Mada (Saudi Arabia)',
            'gateways' => ['paytabs', 'telr', 'mada'],
        ],
        'africa' => [
            'name' => 'Africa',
            'description' => 'Flutterwave, Paystack, M-Pesa, Fawry',
            'gateways' => ['flutterwave', 'paystack', 'mpesa', 'fawry'],
        ],
        'europe' => [
            'name' => 'Europe',
            'description' => 'Klarna, Adyen, iDEAL, Bancontact, SEPA',
            'gateways' => ['klarna', 'adyen', 'ideal', 'bancontact', 'sepa'],
        ],
        'asia_pacific' => [
            'name' => 'Asia Pacific',
            'description' => 'Alipay, WeChat Pay, PayPay, Line Pay, GrabPay',
            'gateways' => ['alipay', 'wechat', 'paypay', 'linepay', 'grabpay'],
        ],
        'americas' => [
            'name' => 'Americas',
            'description' => 'Square, Authorize.net, Moneris, MercadoPago, PagSeguro',
            'gateways' => ['square', 'authorize', 'moneris', 'mercadopago', 'pagseguro'],
        ],
        'crypto' => [
            'name' => 'Cryptocurrency',
            'description' => 'Bitcoin, Ethereum',
            'gateways' => ['bitcoin', 'ethereum'],
        ],
    ];

    public const ALL_GATEWAYS = [
        'stripe' => ['name' => 'Stripe', 'region' => 'Global', 'env_prefix' => 'STRIPE'],
        'paypal' => ['name' => 'PayPal', 'region' => 'Global', 'env_prefix' => 'PAYPAL'],
        'bkash' => ['name' => 'bKash', 'region' => 'Bangladesh', 'env_prefix' => 'BKASH'],
        'nagad' => ['name' => 'Nagad', 'region' => 'Bangladesh', 'env_prefix' => 'NAGAD'],
        'upi' => ['name' => 'UPI', 'region' => 'India', 'env_prefix' => 'UPI'],
        'phonepe' => ['name' => 'PhonePe', 'region' => 'India', 'env_prefix' => 'PHONEPE'],
        'paytm' => ['name' => 'Paytm', 'region' => 'India', 'env_prefix' => 'PAYTM'],
        'jazzcash' => ['name' => 'JazzCash', 'region' => 'Pakistan', 'env_prefix' => 'JAZZCASH'],
        'easypaisa' => ['name' => 'Easypaisa', 'region' => 'Pakistan', 'env_prefix' => 'EASYPAISA'],
        'paytabs' => ['name' => 'PayTabs', 'region' => 'Middle East', 'env_prefix' => 'PAYTABS'],
        'telr' => ['name' => 'Telr', 'region' => 'Middle East', 'env_prefix' => 'TELR'],
        'mada' => ['name' => 'Mada', 'region' => 'Saudi Arabia', 'env_prefix' => 'MADA'],
        'flutterwave' => ['name' => 'Flutterwave', 'region' => 'Africa', 'env_prefix' => 'FLUTTERWAVE'],
        'paystack' => ['name' => 'Paystack', 'region' => 'Africa', 'env_prefix' => 'PAYSTACK'],
        'mpesa' => ['name' => 'M-Pesa', 'region' => 'Africa', 'env_prefix' => 'MPESA'],
        'fawry' => ['name' => 'Fawry', 'region' => 'Egypt', 'env_prefix' => 'FAWRY'],
        'klarna' => ['name' => 'Klarna', 'region' => 'Europe', 'env_prefix' => 'KLARNA'],
        'adyen' => ['name' => 'Adyen', 'region' => 'Europe', 'env_prefix' => 'ADYEN'],
        'ideal' => ['name' => 'iDEAL', 'region' => 'Europe', 'env_prefix' => 'IDEAL'],
        'bancontact' => ['name' => 'Bancontact', 'region' => 'Europe', 'env_prefix' => 'BANCONTACT'],
        'sepa' => ['name' => 'SEPA', 'region' => 'Europe', 'env_prefix' => 'SEPA'],
        'alipay' => ['name' => 'Alipay', 'region' => 'China', 'env_prefix' => 'ALIPAY'],
        'wechat' => ['name' => 'WeChat Pay', 'region' => 'China', 'env_prefix' => 'WECHAT'],
        'paypay' => ['name' => 'PayPay', 'region' => 'Japan', 'env_prefix' => 'PAYPAY'],
        'linepay' => ['name' => 'Line Pay', 'region' => 'Japan', 'env_prefix' => 'LINE_PAY'],
        'grabpay' => ['name' => 'GrabPay', 'region' => 'Southeast Asia', 'env_prefix' => 'GRABPAY'],
        'square' => ['name' => 'Square', 'region' => 'North America', 'env_prefix' => 'SQUARE'],
        'authorize' => ['name' => 'Authorize.net', 'region' => 'North America', 'env_prefix' => 'AUTHORIZE'],
        'moneris' => ['name' => 'Moneris', 'region' => 'North America', 'env_prefix' => 'MONERIS'],
        'mercadopago' => ['name' => 'MercadoPago', 'region' => 'Latin America', 'env_prefix' => 'MERCADOPAGO'],
        'pagseguro' => ['name' => 'PagSeguro', 'region' => 'Latin America', 'env_prefix' => 'PAGSEGURO'],
        'bitcoin' => ['name' => 'Bitcoin', 'region' => 'Cryptocurrency', 'env_prefix' => 'BITCOIN'],
        'ethereum' => ['name' => 'Ethereum', 'region' => 'Cryptocurrency', 'env_prefix' => 'ETHEREUM'],
    ];

    public const GATEWAY_CONFIG_TEMPLATES = [
        'stripe' => [
            'api_key' => 'STRIPE_KEY',
            'api_secret' => 'STRIPE_SECRET',
            'webhook_secret' => 'STRIPE_WEBHOOK_SECRET',
        ],
        'paypal' => [
            'client_id' => 'PAYPAL_CLIENT_ID',
            'client_secret' => 'PAYPAL_CLIENT_SECRET',
            'mode' => 'PAYPAL_MODE',
        ],
        'bkash' => [
            'app_key' => 'BKASH_APP_KEY',
            'app_secret' => 'BKASH_APP_SECRET',
            'username' => 'BKASH_USERNAME',
            'password' => 'BKASH_PASSWORD',
        ],
        'nagad' => [
            'merchant_id' => 'NAGAD_MERCHANT_ID',
            'merchant_password' => 'NAGAD_MERCHANT_PASSWORD',
        ],
        'upi' => [
            'merchant_id' => 'UPI_MERCHANT_ID',
            'merchant_key' => 'UPI_MERCHANT_KEY',
        ],
        'phonepe' => [
            'merchant_id' => 'PHONEPE_MERCHANT_ID',
            'salt_key' => 'PHONEPE_SALT_KEY',
            'salt_index' => 'PHONEPE_SALT_INDEX',
        ],
        'paytm' => [
            'merchant_id' => 'PAYTM_MERCHANT_ID',
            'merchant_key' => 'PAYTM_MERCHANT_KEY',
        ],
        'jazzcash' => [
            'merchant_id' => 'JAZZCASH_MERCHANT_ID',
            'password' => 'JAZZCASH_PASSWORD',
            'integrity_salt' => 'JAZZCASH_INTEGRITY_SALT',
        ],
        'easypaisa' => [
            'merchant_id' => 'EASYPAISA_MERCHANT_ID',
            'merchant_password' => 'EASYPAISA_MERCHANT_PASSWORD',
        ],
        'paytabs' => [
            'profile_id' => 'PAYTABS_PROFILE_ID',
            'server_key' => 'PAYTABS_SERVER_KEY',
        ],
        'telr' => [
            'merchant_id' => 'TELR_MERCHANT_ID',
            'api_key' => 'TELR_API_KEY',
        ],
        'mada' => [
            'merchant_id' => 'MADA_MERCHANT_ID',
            'terminal_id' => 'MADA_TERMINAL_ID',
            'secret_key' => 'MADA_SECRET_KEY',
        ],
        'flutterwave' => [
            'public_key' => 'FLUTTERWAVE_PUBLIC_KEY',
            'encryption_key' => 'FLUTTERWAVE_ENCRYPTION_KEY',
        ],
        'paystack' => [
            'public_key' => 'PAYSTACK_PUBLIC_KEY',
            'secret_key' => 'PAYSTACK_SECRET_KEY',
        ],
        'mpesa' => [
            'business_short_code' => 'MPESA_BUSINESS_SHORT_CODE',
            'passkey' => 'MPESA_PASSKEY',
        ],
        'fawry' => [
            'merchant_code' => 'FAWRY_MERCHANT_CODE',
            'secure_key' => 'FAWRY_SECURE_KEY',
        ],
        'klarna' => [
            'username' => 'KLARNA_USERNAME',
            'password' => 'KLARNA_PASSWORD',
        ],
        'adyen' => [
            'api_key' => 'ADYEN_API_KEY',
            'merchant_account' => 'ADYEN_MERCHANT_ACCOUNT',
        ],
        'ideal' => [
            'merchant_id' => 'IDEAL_MERCHANT_ID',
            'subtoken' => 'IDEAL_SUBTOKEN',
        ],
        'bancontact' => [
            'merchant_id' => 'BANCONTACT_MERCHANT_ID',
            'api_key' => 'BANCONTACT_API_KEY',
        ],
        'sepa' => [
            'api_key' => 'SEPA_API_KEY',
            'api_secret' => 'SEPA_API_SECRET',
        ],
        'alipay' => [
            'app_id' => 'ALIPAY_APP_ID',
            'merchant_private_key' => 'ALIPAY_MERCHANT_PRIVATE_KEY',
            'alipay_public_key' => 'ALIPAY_PUBLIC_KEY',
        ],
        'wechat' => [
            'app_id' => 'WECHAT_APP_ID',
            'mch_id' => 'WECHAT_MCH_ID',
        ],
        'paypay' => [
            'merchant_id' => 'PAYPAY_MERCHANT_ID',
            'merchant_key' => 'PAYPAY_MERCHANT_KEY',
        ],
        'linepay' => [
            'channel_id' => 'LINE_PAY_CHANNEL_ID',
            'channel_secret' => 'LINE_PAY_CHANNEL_SECRET',
        ],
        'grabpay' => [
            'merchant_id' => 'GRABPAY_MERCHANT_ID',
            'merchant_key' => 'GRABPAY_MERCHANT_KEY',
        ],
        'square' => [
            'access_token' => 'SQUARE_ACCESS_TOKEN',
            'location_id' => 'SQUARE_LOCATION_ID',
        ],
        'authorize' => [
            'api_login_id' => 'AUTHORIZE_API_LOGIN_ID',
            'transaction_key' => 'AUTHORIZE_TRANSACTION_KEY',
        ],
        'moneris' => [
            'store_id' => 'MONERIS_STORE_ID',
            'api_token' => 'MONERIS_API_TOKEN',
        ],
        'mercadopago' => [
            'access_token' => 'MERCADOPAGO_ACCESS_TOKEN',
        ],
        'pagseguro' => [
            'email' => 'PAGSEGURO_EMAIL',
            'token' => 'PAGSEGURO_TOKEN',
        ],
        'bitcoin' => [
            'api_key' => 'BITCOIN_API_KEY',
            'api_secret' => 'BITCOIN_API_SECRET',
        ],
        'ethereum' => [
            'api_key' => 'ETHEREUM_API_KEY',
            'api_secret' => 'ETHEREUM_API_SECRET',
        ],
    ];

    public static function getRegionForGateway(string $gateway): string
    {
        foreach (self::REGIONS as $regionKey => $region) {
            if (in_array($gateway, $region['gateways'])) {
                return ucfirst(str_replace('_', ' ', $regionKey));
            }
        }
        return 'Other';
    }

    public static function getGatewaysByRegion(string $region): array
    {
        return self::REGIONS[$region]['gateways'] ?? [];
    }

    public static function getAllGatewayNames(): array
    {
        return array_keys(self::ALL_GATEWAYS);
    }

    public static function getGatewayInfo(string $gateway): ?array
    {
        return self::ALL_GATEWAYS[$gateway] ?? null;
    }
}
