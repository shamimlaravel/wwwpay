<?php

namespace ShamimStack\WwwPay\Gateways\MENA\UAE;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class TabbyGateway implements PaymentGateway
{
    use HasPayments;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $transactionId = 'TABBY_' . Str::random(12);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? $transactionId;

        $orderItems = $data['items'] ?? [
            ['title' => 'Product', 'price' => $amount, 'quantity' => 1]
        ];

        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'tabby',
            'gatewayTransactionId' => $transactionId,
            'orderId' => $orderId,
            'amount' => $amount,
            'currency' => $data['currency'] ?? 'AED',
            'orderItems' => $orderItems,
            'customerEmail' => $data['email'] ?? '',
            'customerPhone' => $data['mobile'] ?? '',
            'returnUrl' => $data['return_url'] ?? url('/payment/callback/tabby'),
            'status' => 'PENDING',
        ]);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_TABBY_' . Str::random(12), [
            'gateway' => 'tabby',
            'originalTransactionId' => $transactionId,
            'refundAmount' => $amount,
            'status' => 'REFUNDED',
        ]);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'tabby',
            'status' => 'CANCELLED',
        ]);
    }

    public function verify(array $data): bool
    {
        return isset($data['id']) || isset($data['order_id']);
    }

    public function getName(): string
    {
        return 'tabby';
    }

    public function supportsSubscriptions(): bool
    {
        return false;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        throw new \Exception('Tabby does not support subscriptions');
    }

    public function unsubscribe(string $subscriptionId): bool
    {
        return false;
    }

    public function handleWebhook($request): bool
    {
        $data = $request->all();
        return isset($data['id']) && isset($data['status']);
    }
}
