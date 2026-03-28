<?php

namespace ShamimStack\WwwPay\Gateways\Americas;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class CloverGateway implements PaymentGateway
{
    use HasPayments;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $transactionId = 'CLOVER_' . Str::random(12);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? $transactionId;

        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'clover',
            'gatewayTransactionId' => $transactionId,
            'orderId' => $orderId,
            'amount' => (int)($amount * 100),
            'currency' => $data['currency'] ?? 'USD',
            'returnUrl' => $data['return_url'] ?? url('/payment/callback/clover'),
            'status' => 'PENDING',
        ]);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_CLOVER_' . Str::random(12), [
            'gateway' => 'clover',
            'originalTransactionId' => $transactionId,
            'refundAmount' => $amount,
            'status' => 'REFUNDED',
        ]);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, ['gateway' => 'clover', 'status' => 'CANCELLED']);
    }

    public function verify(array $data): bool { return isset($data['id']); }
    public function getName(): string { return 'clover'; }
    public function supportsSubscriptions(): bool { return true; }
    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        $subscriptionId = 'SUB_CLOVER_' . Str::random(12);
        return new \ShamimStack\WwwPay\Subscriptions\Subscription(
            $subscriptionId, 'clover', $data['amount'] ?? 0, 'monthly', 'active'
        );
    }
    public function unsubscribe(string $subscriptionId): bool { return true; }
    public function handleWebhook($request): bool
    {
        $data = $request->all();
        return isset($data['type']);
    }
}
