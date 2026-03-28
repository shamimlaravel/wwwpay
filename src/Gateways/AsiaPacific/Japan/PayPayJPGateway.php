<?php

namespace ShamimStack\WwwPay\Gateways\AsiaPacific\Japan;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class PayPayJPGateway implements PaymentGateway
{
    use HasPayments;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $transactionId = 'PAYPJP_' . Str::random(12);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? $transactionId;

        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'paypayjp',
            'gatewayTransactionId' => $transactionId,
            'orderId' => $orderId,
            'amount' => (int)$amount,
            'currency' => 'JPY',
            'returnUrl' => $data['return_url'] ?? url('/payment/callback/paypayjp'),
            'status' => 'PENDING',
        ]);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_PAYPJP_' . Str::random(12), [
            'gateway' => 'paypayjp',
            'originalTransactionId' => $transactionId,
            'refundAmount' => $amount,
            'status' => 'REFUNDED',
        ]);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, ['gateway' => 'paypayjp', 'status' => 'CANCELLED']);
    }

    public function verify(array $data): bool { return isset($data['order_id']); }
    public function getName(): string { return 'paypayjp'; }
    public function supportsSubscriptions(): bool { return false; }
    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        throw new \Exception('PayPay JP does not support subscriptions');
    }
    public function unsubscribe(string $subscriptionId): bool { return false; }
    public function handleWebhook($request): bool
    {
        $data = $request->all();
        return isset($data['event']) && isset($data['data']);
    }
}
