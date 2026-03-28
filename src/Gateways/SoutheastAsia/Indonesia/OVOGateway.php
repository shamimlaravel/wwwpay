<?php

namespace ShamimStack\WwwPay\Gateways\SoutheastAsia\Indonesia;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class OVOGateway implements PaymentGateway
{
    use HasPayments;

    protected array $config;
    protected string $baseUrl = 'https://api.ovo.id';

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $amount = $data['amount'] ?? 0;
        $phone = $data['phone'] ?? '';
        $orderId = 'OVO_' . Str::random(12);

        return new PaymentResponse(true, $orderId, [
            'gatewayTransactionId' => $orderId,
            'data' => [
                'order_id' => $orderId,
                'amount' => $amount,
                'currency' => 'IDR',
                'phone' => $phone,
                'status' => 'PENDING',
            ]
        ]);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_' . Str::random(12), ['original_transaction_id' => $transactionId, 'status' => 'refund_initiated']);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, ['status' => 'cancelled']);
    }

    public function verify(array $data): bool
    {
        return !empty($data['phone']) || !empty($data['order_id']);
    }

    public function getName(): string
    {
        return 'ovo';
    }

    public function supportsSubscriptions(): bool
    {
        return false;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        throw new \Exception('OVO does not support subscriptions');
    }

    public function unsubscribe(string $subscriptionId): bool
    {
        return false;
    }

    public function handleWebhook($request): bool
    {
        return isset($request->all()['order_id']);
    }
}
