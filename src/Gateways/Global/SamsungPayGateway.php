<?php

namespace ShamimStack\WwwPay\Gateways\Global;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class SamsungPayGateway implements PaymentGateway
{
    use HasPayments;

    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $amount = $data['amount'] ?? 0;
        $transactionId = 'SPAY_' . Str::random(16);

        return new PaymentResponse(true, $transactionId, [
            'gatewayTransactionId' => $transactionId,
            'data' => [
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'currency' => $data['currency'] ?? 'USD',
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
        return isset($data['payment_token']);
    }

    public function getName(): string
    {
        return 'samsungpay';
    }

    public function supportsSubscriptions(): bool
    {
        return false;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        throw new \Exception('SamsungPay does not support subscriptions');
    }

    public function unsubscribe(string $subscriptionId): bool
    {
        return false;
    }

    public function handleWebhook($request): bool
    {
        return isset($request->all()['transaction_id']);
    }
}
