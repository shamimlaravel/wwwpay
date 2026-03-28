<?php

namespace ShamimStack\WwwPay\Gateways\Bangladesh;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class ShurjoPayGateway implements PaymentGateway
{
    use HasPayments;

    protected array $config;
    protected string $baseUrl = 'https://shurjopay.com';

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $amount = $data['amount'] ?? 0;
        $orderId = 'SP_' . Str::random(12);
        $customerName = $data['customer_name'] ?? '';
        $customerEmail = $data['customer_email'] ?? '';
        $customerMobile = $data['customer_mobile'] ?? '';

        return new PaymentResponse(
            true,
            $orderId,
            [
                'gatewayTransactionId' => $orderId,
                'data' => [
                    'order_id' => $orderId,
                    'amount' => $amount,
                    'currency' => 'BDT',
                    'customer_name' => $customerName,
                    'customer_email' => $customerEmail,
                    'customer_mobile' => $customerMobile,
                    'status' => 'PENDING',
                    'checkout_url' => "https://shurjopay.com/checkout/{$orderId}",
                ]
            ]
        );
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_' . Str::random(12), [
            'original_transaction_id' => $transactionId,
            'status' => 'refund_initiated'
        ]);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, ['status' => 'cancelled']);
    }

    public function verify(array $data): bool
    {
        return !empty($data['order_id']) || !empty($data['customer_email']);
    }

    public function getName(): string
    {
        return 'shurjopay';
    }

    public function supportsSubscriptions(): bool
    {
        return false;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        throw new \Exception('ShurjoPay does not support subscriptions');
    }

    public function unsubscribe(string $subscriptionId): bool
    {
        return false;
    }

    public function handleWebhook($request): bool
    {
        return isset($request->all()['order_id']);
    }
}
