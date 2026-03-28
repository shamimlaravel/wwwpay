<?php

namespace ShamimStack\WwwPay\Gateways\LatinAmerica\Brazil;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class PicPayGateway implements PaymentGateway
{
    use HasPayments;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $transactionId = 'PICPAY_' . Str::random(12);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? $transactionId;

        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'picpay',
            'gatewayTransactionId' => $transactionId,
            'orderId' => $orderId,
            'amount' => $amount,
            'currency' => 'BRL',
            'callbackUrl' => $data['callback_url'] ?? url('/payment/webhook/picpay'),
            'returnUrl' => $data['return_url'] ?? url('/payment/callback/picpay'),
            'status' => 'PENDING',
        ]);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_PICPAY_' . Str::random(12), [
            'gateway' => 'picpay',
            'originalTransactionId' => $transactionId,
            'refundAmount' => $amount,
            'status' => 'REFUNDED',
        ]);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'picpay',
            'status' => 'CANCELLED',
        ]);
    }

    public function verify(array $data): bool
    {
        return isset($data['referenceId']) || isset($data['order_id']);
    }

    public function getName(): string
    {
        return 'picpay';
    }

    public function supportsSubscriptions(): bool
    {
        return false;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        throw new \Exception('PicPay does not support subscriptions');
    }

    public function unsubscribe(string $subscriptionId): bool
    {
        return false;
    }

    public function handleWebhook($request): bool
    {
        $data = $request->all();
        return isset($data['referenceId']) && isset($data['status']);
    }
}
