<?php

namespace ShamimStack\WwwPay\Gateways\SoutheastAsia\Philippines;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class MayaGateway implements PaymentGateway
{
    use HasPayments;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $transactionId = 'MAYA_' . Str::random(12);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? $transactionId;
        $mobile = $data['mobile'] ?? '';

        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'maya',
            'gatewayTransactionId' => $transactionId,
            'orderId' => $orderId,
            'amount' => $amount,
            'currency' => $data['currency'] ?? 'PHP',
            'mobile' => $mobile,
            'returnUrl' => $data['return_url'] ?? url('/payment/callback/maya'),
            'status' => 'PENDING',
        ]);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_MAYA_' . Str::random(12), [
            'gateway' => 'maya',
            'originalTransactionId' => $transactionId,
            'refundAmount' => $amount,
            'status' => 'REFUNDED',
        ]);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'maya',
            'status' => 'CANCELLED',
        ]);
    }

    public function verify(array $data): bool
    {
        return !empty($data['id']) || !empty($data['order_id']);
    }

    public function getName(): string
    {
        return 'maya';
    }

    public function supportsSubscriptions(): bool
    {
        return true;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        $subscriptionId = 'SUB_MAYA_' . Str::random(12);
        return new \ShamimStack\WwwPay\Subscriptions\Subscription(
            $subscriptionId,
            'maya',
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
        return isset($data['id']) || isset($data['order_id']);
    }
}
