<?php

namespace ShamimStack\WwwPay\Gateways\LatinAmerica\Mexico;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class ConektaGateway implements PaymentGateway
{
    use HasPayments;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $transactionId = 'CONEKTA_' . Str::random(12);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? $transactionId;
        $currency = $data['currency'] ?? 'MXN';

        $lineItems = $data['items'] ?? [
            ['name' => 'Product', 'unit_price' => (int)($amount * 100), 'quantity' => 1]
        ];

        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'conekta',
            'gatewayTransactionId' => $transactionId,
            'orderId' => $orderId,
            'amount' => $amount,
            'currency' => $currency,
            'lineItems' => $lineItems,
            'customerEmail' => $data['email'] ?? '',
            'returnUrl' => $data['return_url'] ?? url('/payment/callback/conekta'),
            'status' => 'PENDING',
        ]);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_CONEKTA_' . Str::random(12), [
            'gateway' => 'conekta',
            'originalTransactionId' => $transactionId,
            'refundAmount' => $amount,
            'status' => 'REFUNDED',
        ]);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'conekta',
            'status' => 'CANCELLED',
        ]);
    }

    public function verify(array $data): bool
    {
        return isset($data['id']) || isset($data['order_id']);
    }

    public function getName(): string
    {
        return 'conekta';
    }

    public function supportsSubscriptions(): bool
    {
        return true;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        $subscriptionId = 'SUB_CONEKTA_' . Str::random(12);
        return new \ShamimStack\WwwPay\Subscriptions\Subscription(
            $subscriptionId,
            'conekta',
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
        return isset($data['type']) && isset($data['data']);
    }
}
