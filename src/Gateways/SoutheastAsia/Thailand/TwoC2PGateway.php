<?php

namespace ShamimStack\WwwPay\Gateways\SoutheastAsia\Thailand;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class TwoC2PGateway implements PaymentGateway
{
    use HasPayments;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $transactionId = '2C2P_' . Str::random(12);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? $transactionId;

        $paymentData = [
            'version' => '9.9',
            'merchantId' => $this->config['merchant_id'] ?? '',
            'currency' => $data['currency'] ?? 'THB',
            'amount' => number_format($amount, 2, '.', ''),
            'orderId' => $orderId,
            'paymentDescription' => $data['description'] ?? '2C2P Payment',
            'returnUrl' => $data['return_url'] ?? url('/payment/callback/2c2p'),
        ];

        return new PaymentResponse(true, $transactionId, [
            'gateway' => '2c2p',
            'gatewayTransactionId' => $transactionId,
            'orderId' => $orderId,
            'amount' => $amount,
            'currency' => $data['currency'] ?? 'THB',
            'paymentData' => $paymentData,
            'status' => 'PENDING',
        ]);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_2C2P_' . Str::random(12), [
            'gateway' => '2c2p',
            'originalTransactionId' => $transactionId,
            'refundAmount' => $amount,
            'status' => 'REFUNDED',
        ]);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, [
            'gateway' => '2c2p',
            'status' => 'CANCELLED',
        ]);
    }

    public function verify(array $data): bool
    {
        return isset($data['orderId']) || isset($data['order_id']);
    }

    public function getName(): string
    {
        return '2c2p';
    }

    public function supportsSubscriptions(): bool
    {
        return true;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        $subscriptionId = 'SUB_2C2P_' . Str::random(12);
        return new \ShamimStack\WwwPay\Subscriptions\Subscription(
            $subscriptionId,
            '2c2p',
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
        return isset($data['orderId']) && isset($data['respCode']);
    }
}
