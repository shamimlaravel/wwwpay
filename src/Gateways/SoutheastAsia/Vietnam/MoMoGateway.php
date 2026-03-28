<?php

namespace ShamimStack\WwwPay\Gateways\SoutheastAsia\Vietnam;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class MoMoGateway implements PaymentGateway
{
    use HasPayments;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $transactionId = 'MOMO_' . Str::random(12);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? $transactionId;
        $returnUrl = $data['return_url'] ?? url('/payment/callback/momo');

        $requestId = Str::random(12);
        $requestType = 'captureWallet';

        $rawData = "accessKey=" . ($this->config['access_key'] ?? '') .
            "&amount=" . $amount .
            "&extraData=" . ($data['extra_data'] ?? '') .
            "&orderId=" . $orderId .
            "&orderInfo=" . ($data['description'] ?? 'MoMo Payment') .
            "&partnerCode=" . ($this->config['partner_code'] ?? '') .
            "&requestId=" . $requestId .
            "&requestType=" . $requestType;

        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'momo',
            'gatewayTransactionId' => $transactionId,
            'orderId' => $orderId,
            'amount' => $amount,
            'currency' => $data['currency'] ?? 'VND',
            'returnUrl' => $returnUrl,
            'requestId' => $requestId,
            'requestType' => $requestType,
            'rawData' => $rawData,
            'status' => 'PENDING',
        ]);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_MOMO_' . Str::random(12), [
            'gateway' => 'momo',
            'originalTransactionId' => $transactionId,
            'refundAmount' => $amount,
            'status' => 'REFUNDED',
        ]);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'momo',
            'status' => 'CANCELLED',
        ]);
    }

    public function verify(array $data): bool
    {
        return isset($data['orderId']) && !empty($data['orderId']);
    }

    public function getName(): string
    {
        return 'momo';
    }

    public function supportsSubscriptions(): bool
    {
        return true;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        $subscriptionId = 'SUB_MOMO_' . Str::random(12);
        return new \ShamimStack\WwwPay\Subscriptions\Subscription(
            $subscriptionId,
            'momo',
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
        return isset($data['orderId']) && isset($data['transId']);
    }
}
