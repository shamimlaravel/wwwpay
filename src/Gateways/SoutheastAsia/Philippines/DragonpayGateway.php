<?php

namespace ShamimStack\WwwPay\Gateways\SoutheastAsia\Philippines;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class DragonpayGateway implements PaymentGateway
{
    use HasPayments;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $transactionId = 'DRAGON_' . Str::random(12);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? $transactionId;
        $description = $data['description'] ?? 'Dragonpay Payment';
        $email = $data['email'] ?? '';

        $merchantId = $this->config['merchant_id'] ?? '';
        $merchantKey = $this->config['merchant_key'] ?? '';

        $params = [
            'merchantid' => $merchantId,
            'txnid' => $orderId,
            'amount' => number_format($amount, 2, '.', ''),
            'ccy' => $data['currency'] ?? 'PHP',
            'description' => $description,
            'email' => $email,
            'returnurl' => $data['return_url'] ?? url('/payment/callback/dragonpay'),
        ];

        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'dragonpay',
            'gatewayTransactionId' => $transactionId,
            'orderId' => $orderId,
            'amount' => $amount,
            'currency' => $data['currency'] ?? 'PHP',
            'params' => $params,
            'status' => 'PENDING',
        ]);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_DRAGON_' . Str::random(12), [
            'gateway' => 'dragonpay',
            'originalTransactionId' => $transactionId,
            'refundAmount' => $amount,
            'status' => 'REFUNDED',
        ]);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'dragonpay',
            'status' => 'CANCELLED',
        ]);
    }

    public function verify(array $data): bool
    {
        return isset($data['txnid']) || isset($data['order_id']);
    }

    public function getName(): string
    {
        return 'dragonpay';
    }

    public function supportsSubscriptions(): bool
    {
        return false;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        throw new \Exception('Dragonpay does not support subscriptions');
    }

    public function unsubscribe(string $subscriptionId): bool
    {
        return false;
    }

    public function handleWebhook($request): bool
    {
        $data = $request->all();
        return isset($data['txnid']) && isset($data['status']);
    }
}
