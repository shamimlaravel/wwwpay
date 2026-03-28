<?php

namespace ShamimStack\WwwPay\Gateways\AsiaPacific;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class PayLahGateway implements PaymentGateway
{
    use HasPayments;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $transactionId = 'PAYLAH_' . Str::random(12);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? $transactionId;

        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'paylah',
            'gatewayTransactionId' => $transactionId,
            'orderId' => $orderId,
            'amount' => $amount,
            'currency' => 'SGD',
            'mobile' => $data['mobile'] ?? '',
            'returnUrl' => $data['return_url'] ?? url('/payment/callback/paylah'),
            'status' => 'PENDING',
        ]);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_PAYLAH_' . Str::random(12), [
            'gateway' => 'paylah',
            'originalTransactionId' => $transactionId,
            'refundAmount' => $amount,
            'status' => 'REFUNDED',
        ]);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, ['gateway' => 'paylah', 'status' => 'CANCELLED']);
    }

    public function verify(array $data): bool { return isset($data['order_id']); }
    public function getName(): string { return 'paylah'; }
    public function supportsSubscriptions(): bool { return false; }
    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        throw new \Exception('PayLah does not support subscriptions');
    }
    public function unsubscribe(string $subscriptionId): bool { return false; }
    public function handleWebhook($request): bool
    {
        $data = $request->all();
        return isset($data['order_id']);
    }
}
