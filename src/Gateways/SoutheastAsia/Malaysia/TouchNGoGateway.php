<?php

namespace ShamimStack\WwwPay\Gateways\SoutheastAsia\Malaysia;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class TouchNGoGateway implements PaymentGateway
{
    use HasPayments;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $transactionId = 'TNG_' . Str::random(12);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? $transactionId;

        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'touchgo',
            'gatewayTransactionId' => $transactionId,
            'orderId' => $orderId,
            'amount' => $amount,
            'currency' => $data['currency'] ?? 'MYR',
            'returnUrl' => $data['return_url'] ?? url('/payment/callback/touchgo'),
            'status' => 'PENDING',
        ]);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_TNG_' . Str::random(12), [
            'gateway' => 'touchgo',
            'originalTransactionId' => $transactionId,
            'refundAmount' => $amount,
            'status' => 'REFUNDED',
        ]);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'touchgo',
            'status' => 'CANCELLED',
        ]);
    }

    public function verify(array $data): bool
    {
        return !empty($data['order_id']);
    }

    public function getName(): string
    {
        return 'touchgo';
    }

    public function supportsSubscriptions(): bool
    {
        return true;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        $subscriptionId = 'SUB_TNG_' . Str::random(12);
        return new \ShamimStack\WwwPay\Subscriptions\Subscription(
            $subscriptionId,
            'touchgo',
            $data['amount'] ?? 0,
            'monthly',
            'active'
        );
    }

    public function unsubscribe(string $subscriptionId): bool
    {
        return true;
    }

    public function handleWebhook($request): bool
    {
        $data = $request->all();
        return isset($data['order_id']) && isset($data['status']);
    }
}
