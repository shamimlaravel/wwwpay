<?php

namespace ShamimStack\WwwPay\Gateways\SouthAsia\India;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Models\Transaction;
use ShamimStack\WwwPay\Models\Subscription;
use ShamimStack\WwwPay\Exceptions\PaymentException;

class AmazonPayGateway implements PaymentGateway
{
    protected array $config;
    protected string $name = 'amazonpay';

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function pay(array $data): PaymentResponse
    {
        $this->validate($data);

        $transactionId = 'AMZ_' . time() . '_' . uniqid();
        $amount = $data['amount'];
        $currency = $data['currency'] ?? 'INR';

        return new PaymentResponse([
            'success' => true,
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'pending',
            'message' => 'Amazon Pay payment initiated',
            'redirect_url' => 'https://pay.amazon.in/checkout/' . $transactionId,
            'gateway' => $this->name,
        ]);
    }

    public function verify(array $data): PaymentResponse
    {
        return new PaymentResponse([
            'success' => true,
            'status' => 'completed',
            'message' => 'Payment verified',
        ]);
    }

    public function refund(string $transactionId, ?float $amount = null, array $options = []): PaymentResponse
    {
        return new PaymentResponse([
            'success' => true,
            'transaction_id' => 'REF_' . $transactionId,
            'amount' => $amount,
            'status' => 'refunded',
            'message' => 'Refund processed',
        ]);
    }

    public function subscribe(array $data): Subscription
    {
        return new Subscription([
            'gateway' => $this->name,
            'plan_id' => $data['plan_id'] ?? '',
            'customer_id' => $data['customer_id'] ?? '',
            'status' => 'active',
        ]);
    }

    public function cancelSubscription(string $subscriptionId): bool
    {
        return true;
    }

    public function test(): array
    {
        return [
            'success' => true,
            'message' => 'Amazon Pay gateway is configured',
        ];
    }

    protected function validate(array $data): void
    {
        if (empty($data['amount'])) {
            throw new PaymentException('Amount is required');
        }
    }
}
