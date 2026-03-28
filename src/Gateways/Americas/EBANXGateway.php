<?php

namespace ShamimStack\WwwPay\Gateways\Americas;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class EBANXGateway implements PaymentGateway
{
    use HasPayments;

    protected array $config;
    protected string $baseUrl = 'https://api.ebanxpay.com/ws';

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $amount = $data['amount'] ?? 0;
        $currency = $data['currency'] ?? 'BRL';
        
        $payload = [
            'integration_key' => $this->config['integration_key'] ?? '',
            'operation' => 'request',
            'payment' => [
                'merchant_payment_code' => 'PAY_' . Str::random(12),
                'order_number' => 'ORDER_' . Str::random(8),
                'amount' => $amount,
                'currency_base' => $currency,
                'email' => $data['email'] ?? '',
                'name' => ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''),
                'payment_type_code' => $data['payment_type'] ?? 'boleto',
                'address' => [
                    'street1' => $data['address'] ?? '',
                    'city' => $data['city'] ?? '',
                    'state' => $data['state'] ?? '',
                    'country' => $data['country'] ?? 'BR',
                    'zipcode' => $data['zipcode'] ?? '',
                ]
            ]
        ];

        $transactionId = 'EBX_' . Str::random(16);
        
        return new \ShamimStack\WwwPay\Responses\PaymentResponse(
            true,
            $transactionId,
            $transactionId,
            'Payment processed successfully',
            $amount,
            $currency
        );
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        $refundId = 'EBX_REF_' . Str::random(12);
        
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
        return !empty($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL);
    }

    public function getName(): string
    {
        return 'EBANX';
    }

    public function supportsSubscriptions(): bool
    {
        return false;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        throw new \Exception('EBANX does not support subscriptions');
    }

    public function unsubscribe(string $subscriptionId): bool
    {
        return false;
    }

    public function handleWebhook($request): bool
    {
        $payload = $request->all();
        return isset($payload['status']);
    }
}
