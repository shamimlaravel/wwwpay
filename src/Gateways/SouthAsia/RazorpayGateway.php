<?php

namespace ShamimStack\WwwPay\Gateways\SouthAsia;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class RazorpayGateway implements PaymentGateway
{
    use HasPayments;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $transactionId = 'RAZORPAY_' . Str::random(12);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? $transactionId;

        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'razorpay',
            'gatewayTransactionId' => $transactionId,
            'orderId' => $orderId,
            'amount' => (int)($amount * 100),
            'currency' => 'INR',
            'customerEmail' => $data['email'] ?? '',
            'customerPhone' => $data['mobile'] ?? '',
            'returnUrl' => $data['return_url'] ?? url('/payment/callback/razorpay'),
            'status' => 'PENDING',
        ]);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_RAZORPAY_' . Str::random(12), [
            'gateway' => 'razorpay',
            'originalTransactionId' => $transactionId,
            'refundAmount' => $amount,
            'status' => 'REFUNDED',
        ]);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, ['gateway' => 'razorpay', 'status' => 'CANCELLED']);
    }

    public function verify(array $data): bool { return isset($data['razorpay_order_id']); }
    public function getName(): string { return 'razorpay'; }
    public function supportsSubscriptions(): bool { return true; }
    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        $subscriptionId = 'SUB_RAZORPAY_' . Str::random(12);
        return new \ShamimStack\WwwPay\Subscriptions\Subscription(
            $subscriptionId, 'razorpay', $data['amount'] ?? 0, 'monthly', 'active'
        );
    }
    public function unsubscribe(string $subscriptionId): bool { return true; }
    public function handleWebhook($request): bool
    {
        $data = $request->all();
        return isset($data['event']);
    }
}
