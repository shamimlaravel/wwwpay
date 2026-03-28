<?php

namespace ShamimStack\WwwPay\Gateways\SoutheastAsia\Vietnam;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class VimoGateway implements PaymentGateway
{
    use HasPayments;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $transactionId = 'VIMO_' . Str::random(12);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? $transactionId;

        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'vimo',
            'gatewayTransactionId' => $transactionId,
            'orderId' => $orderId,
            'amount' => $amount,
            'currency' => 'VND',
            'mobile' => $data['mobile'] ?? '',
            'returnUrl' => $data['return_url'] ?? url('/payment/callback/vimo'),
            'status' => 'PENDING',
        ]);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_VIMO_' . Str::random(12), [
            'gateway' => 'vimo',
            'originalTransactionId' => $transactionId,
            'refundAmount' => $amount,
            'status' => 'REFUNDED',
        ]);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, ['gateway' => 'vimo', 'status' => 'CANCELLED']);
    }

    public function verify(array $data): bool { return isset($data['order_id']); }
    public function getName(): string { return 'vimo'; }
    public function supportsSubscriptions(): bool { return false; }
    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        throw new \Exception('Vimo does not support subscriptions');
    }
    public function unsubscribe(string $subscriptionId): bool { return false; }
    public function handleWebhook($request): bool
    {
        $data = $request->all();
        return isset($data['order_id']);
    }
}
