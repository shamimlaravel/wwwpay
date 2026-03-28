<?php

namespace ShamimStack\WwwPay\Gateways\SoutheastAsia\Malaysia;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class IPay88Gateway implements PaymentGateway
{
    use HasPayments;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $transactionId = 'IPAY88_' . Str::random(12);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? $transactionId;

        $params = [
            'MerchantCode' => $this->config['merchant_code'] ?? '',
            'PaymentId' => $data['payment_id'] ?? '',
            'RefNo' => $orderId,
            'Amount' => number_format($amount, 2, '.', ''),
            'Currency' => $data['currency'] ?? 'MYR',
            'ProdDesc' => $data['description'] ?? 'iPay88 Payment',
            'UserName' => $data['name'] ?? '',
            'UserEmail' => $data['email'] ?? '',
            'UserContact' => $data['mobile'] ?? '',
            'Remark' => $data['remark'] ?? '',
            'Lang' => 'UTF-8',
            'ReturnURL' => $data['return_url'] ?? url('/payment/callback/ipay88'),
            'CallbackURL' => $data['callback_url'] ?? url('/payment/webhook/ipay88'),
        ];

        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'ipay88',
            'gatewayTransactionId' => $transactionId,
            'orderId' => $orderId,
            'amount' => $amount,
            'currency' => $data['currency'] ?? 'MYR',
            'params' => $params,
            'status' => 'PENDING',
        ]);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_IPAY88_' . Str::random(12), [
            'gateway' => 'ipay88',
            'originalTransactionId' => $transactionId,
            'refundAmount' => $amount,
            'status' => 'REFUNDED',
        ]);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'ipay88',
            'status' => 'CANCELLED',
        ]);
    }

    public function verify(array $data): bool
    {
        return isset($data['RefNo']) || isset($data['order_id']);
    }

    public function getName(): string
    {
        return 'ipay88';
    }

    public function supportsSubscriptions(): bool
    {
        return false;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        throw new \Exception('iPay88 does not support subscriptions');
    }

    public function unsubscribe(string $subscriptionId): bool
    {
        return false;
    }

    public function handleWebhook($request): bool
    {
        $data = $request->all();
        return isset($data['RefNo']) && isset($data['Status']);
    }
}
