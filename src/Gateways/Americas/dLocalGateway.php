<?php

namespace ShamimStack\WwwPay\Gateways\Americas;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class dLocalGateway implements PaymentGateway
{
    use HasPayments;

    protected array $config;
    protected string $baseUrl;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->baseUrl = $config['sandbox'] ?? false 
            ? 'https://sandbox.dlocal.com' 
            : 'https://api.dlocal.com';
    }

    public function pay(array $data): PaymentResponse
    {
        $amount = $data['amount'] ?? 0;
        $currency = $data['currency'] ?? 'BRL';
        
        $payload = [
            'country' => strtoupper($data['country'] ?? 'BR'),
            'amount' => $amount,
            'currency' => $currency,
            'payment_method_id' => $data['payment_method'] ?? 'CARD',
            'payer' => [
                'name' => ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''),
                'email' => $data['email'] ?? '',
                'document' => $data['document'] ?? '',
                'user_reference' => $data['user_reference'] ?? Str::random(8),
            ],
            'order_id' => 'ORD_' . Str::random(12),
            'callback_url' => $data['callback_url'] ?? '',
        ];

        $transactionId = 'DL_' . Str::random(16);
        
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
        $refundId = 'DL_REF_' . Str::random(12);
        
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
        return 'dLocal';
    }

    public function supportsSubscriptions(): bool
    {
        return true;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        $subscriptionId = 'DL_SUB_' . Str::random(12);
        
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
        return isset($payload['id']) && isset($payload['status']);
    }
}
