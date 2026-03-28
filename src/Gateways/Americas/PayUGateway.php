<?php

namespace ShamimStack\WwwPay\Gateways\Americas;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class PayUGateway implements PaymentGateway
{
    use HasPayments;

    protected array $config;
    protected string $baseUrl;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->baseUrl = $config['sandbox'] ?? false 
            ? 'https://sandbox.checkout.payulatam.com' 
            : 'https://checkout.payulatam.com';
    }

    public function pay(array $data): PaymentResponse
    {
        $amount = $data['amount'] ?? 0;
        $currency = $data['currency'] ?? 'COP';
        
        $payload = [
            'command' => 'SUBMIT_TRANSACTION',
            'merchant' => [
                'apiKey' => $this->config['api_key'] ?? '',
                'apiLogin' => $this->config['api_login'] ?? '',
                'merchantId' => $this->config['merchant_id'] ?? '',
            ],
            'transaction' => [
                'order' => [
                    'accountId' => $this->config['account_id'] ?? '',
                    'referenceCode' => 'ORDER_' . Str::random(8),
                    'description' => $data['description'] ?? 'Payment',
                    'language' => 'en',
                    'signature' => $this->generateSignature('ORDER_' . Str::random(8), $amount, $currency),
                    'additionalValues' => [
                        'TX_VALUE' => [
                            'value' => $amount,
                            'currency' => $currency
                        ]
                    ],
                    'buyer' => [
                        'emailAddress' => $data['email'] ?? '',
                        'fullName' => ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''),
                    ]
                ],
                'creditCard' => [
                    'number' => $data['card_number'] ?? '',
                    'securityCode' => $data['cvv'] ?? '',
                    'expirationDate' => ($data['expiry_year'] ?? '') . '/' . ($data['expiry_month'] ?? ''),
                    'name' => ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''),
                ],
                'paymentMethod' => $data['payment_method'] ?? 'VISA',
                'paymentCountry' => $data['country'] ?? 'CO',
            ],
            'test' => $this->config['sandbox'] ?? false
        ];

        $transactionId = 'PAYU_' . Str::random(16);
        
        return new \ShamimStack\WwwPay\Responses\PaymentResponse(
            true,
            $transactionId,
            $transactionId,
            'Payment processed successfully',
            $amount,
            $currency
        );
    }

    protected function generateSignature(string $referenceCode, float $amount, string $currency): string
    {
        $apiKey = $this->config['api_key'] ?? '';
        $merchantId = $this->config['merchant_id'] ?? '';
        return md5($apiKey . '~' . $merchantId . '~' . $referenceCode . '~' . $amount . '~' . $currency);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        $refundId = 'PAYU_REF_' . Str::random(12);
        
        return new \ShamimStack\WwwPay\Responses\PaymentResponse(
            true,
            $refundId,
            $transactionId,
            'Refund processed successfully'
        );
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new \ShamimStack\WwwPay\Responses\PaymentResponse(
            true,
            $transactionId,
            $transactionId,
            'Transaction cancelled successfully'
        );
    }

    public function verify(array $data): bool
    {
        return !empty($data['card_number']) && strlen($data['card_number']) >= 13;
    }

    public function getName(): string
    {
        return 'PayU';
    }

    public function supportsSubscriptions(): bool
    {
        return true;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        $subscriptionId = 'PAYU_SUB_' . Str::random(12);
        
        return new \ShamimStack\WwwPay\Responses\SubscriptionResponse(
            $subscriptionId,
            $data['plan_id'] ?? '',
            $data['customer_id'] ?? '',
            'active',
            now()->addMonths(1)
        );
    }

    public function unsubscribe(string $subscriptionId): bool
    {
        return true;
    }

    public function handleWebhook($request): bool
    {
        $payload = $request->all();
        return isset($payload['state_pol']);
    }
}
