<?php

namespace ShamimStack\WwwPay\Gateways\LatinAmerica\Colombia;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class PSEGateway implements PaymentGateway
{
    use HasPayments;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $transactionId = 'PSE_' . Str::random(12);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? $transactionId;

        $bankCode = $data['bank_code'] ?? '';
        $bankInterface = $data['bank_interface'] ?? '0';

        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'pse',
            'gatewayTransactionId' => $transactionId,
            'orderId' => $orderId,
            'amount' => $amount,
            'currency' => 'COP',
            'bankCode' => $bankCode,
            'bankInterface' => $bankInterface,
            'payer' => $data['payer'] ?? [],
            'buyer' => $data['buyer'] ?? [],
            'returnUrl' => $data['return_url'] ?? url('/payment/callback/pse'),
            'status' => 'PENDING',
        ]);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_PSE_' . Str::random(12), [
            'gateway' => 'pse',
            'originalTransactionId' => $transactionId,
            'refundAmount' => $amount,
            'status' => 'REFUNDED',
        ]);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'pse',
            'status' => 'CANCELLED',
        ]);
    }

    public function verify(array $data): bool
    {
        return isset($data['transactionId']) || isset($data['order_id']);
    }

    public function getName(): string
    {
        return 'pse';
    }

    public function supportsSubscriptions(): bool
    {
        return false;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        throw new \Exception('PSE does not support subscriptions');
    }

    public function unsubscribe(string $subscriptionId): bool
    {
        return false;
    }

    public function handleWebhook($request): bool
    {
        $data = $request->all();
        return isset($data['transactionId']) && isset($data['state']);
    }
}
