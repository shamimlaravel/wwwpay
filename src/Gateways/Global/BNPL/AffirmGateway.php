<?php

namespace ShamimStack\WwwPay\Gateways\Global\BNPL;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class AffirmGateway implements PaymentGateway
{
    use HasPayments;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $transactionId = 'AFFIRM_' . Str::random(12);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? $transactionId;

        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'affirm',
            'gatewayTransactionId' => $transactionId,
            'orderId' => $orderId,
            'amount' => (int)($amount * 100),
            'currency' => $data['currency'] ?? 'USD',
            'items' => $data['items'] ?? [],
            'customerEmail' => $data['email'] ?? '',
            'returnUrl' => $data['return_url'] ?? url('/payment/callback/affirm'),
            'status' => 'PENDING',
        ]);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_AFFIRM_' . Str::random(12), [
            'gateway' => 'affirm',
            'originalTransactionId' => $transactionId,
            'refundAmount' => $amount,
            'status' => 'REFUNDED',
        ]);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'affirm',
            'status' => 'CANCELLED',
        ]);
    }

    public function verify(array $data): bool
    {
        return isset($data['checkout_token']) || isset($data['order_id']);
    }

    public function getName(): string
    {
        return 'affirm';
    }

    public function supportsSubscriptions(): bool
    {
        return false;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        throw new \Exception('Affirm does not support subscriptions');
    }

    public function unsubscribe(string $subscriptionId): bool
    {
        return false;
    }

    public function handleWebhook($request): bool
    {
        $data = $request->all();
        return isset($data['type']) && isset($data['id']);
    }
}
