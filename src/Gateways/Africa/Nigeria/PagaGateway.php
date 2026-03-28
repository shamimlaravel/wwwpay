<?php

namespace ShamimStack\WwwPay\Gateways\Africa\Nigeria;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Models\Subscription;
use ShamimStack\WwwPay\Exceptions\PaymentException;

class PagaGateway implements PaymentGateway
{
    protected array $config;
    protected string $name = 'paga';

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

        $transactionId = 'PGA_' . time() . '_' . uniqid();

        return new PaymentResponse([
            'success' => true,
            'transaction_id' => $transactionId,
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'NGN',
            'status' => 'pending',
            'message' => 'Paga payment initiated',
            'redirect_url' => 'https://mypaga.com/' . $transactionId,
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
        return ['success' => true, 'message' => 'Paga gateway is configured'];
    }

    protected function validate(array $data): void
    {
        if (empty($data['amount'])) {
            throw new PaymentException('Amount is required');
        }
    }
}
