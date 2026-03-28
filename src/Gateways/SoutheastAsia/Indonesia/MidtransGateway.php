<?php

namespace ShamimStack\WwwPay\Gateways\SoutheastAsia\Indonesia;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class MidtransGateway implements PaymentGateway
{
    use HasPayments;

    protected array $config;
    protected string $baseUrl = 'https://api.midtrans.com';

    public function __construct(array $config)
    {
        $this->config = $config;
        if (($config['sandbox'] ?? false)) {
            $this->baseUrl = 'https://api.sandbox.midtrans.com';
        }
    }

    public function pay(array $data): PaymentResponse
    {
        $amount = $data['amount'] ?? 0;
        $orderId = 'MT_' . Str::random(12);
        $customerName = $data['customer_name'] ?? '';
        $customerEmail = $data['customer_email'] ?? '';

        return new PaymentResponse(true, $orderId, [
            'gatewayTransactionId' => $orderId,
            'data' => [
                'order_id' => $orderId,
                'amount' => $amount,
                'currency' => 'IDR',
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'status' => 'PENDING',
                'payment_url' => "https://app.midtrans.com/gate/{$orderId}",
            ]
        ]);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_' . Str::random(12), ['original_transaction_id' => $transactionId, 'status' => 'refund_initiated']);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, ['status' => 'cancelled']);
    }

    public function verify(array $data): bool
    {
        return !empty($data['order_id']);
    }

    public function getName(): string
    {
        return 'midtrans';
    }

    public function supportsSubscriptions(): bool
    {
        return true;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        $subscriptionId = 'MT_SUB_' . Str::random(12);
        return new class($subscriptionId, $data) implements \ShamimStack\WwwPay\Contracts\Subscription {
            private string $id;
            private array $data;
            private string $status = 'active';
            private ?\DateTime $endDate;

            public function __construct(string $id, array $data)
            {
                $this->id = $id;
                $this->data = $data;
                $this->endDate = new \DateTime('+1 month');
            }

            public function getGatewaySubscriptionId(): ?string { return $this->id; }
            public function getPlanId(): ?string { return $this->data['plan_id'] ?? null; }
            public function getCustomerId(): ?string { return $this->data['customer_id'] ?? null; }
            public function getStatus(): string { return $this->status; }
            public function isActive(): bool { return $this->status === 'active'; }
            public function isCancelled(): bool { return $this->status === 'cancelled'; }
            public function isExpired(): bool { return $this->endDate && $this->endDate < new \DateTime(); }
            public function cancel(?string $reason = null): bool { $this->status = 'cancelled'; return true; }
            public function getStartDate(): ?\DateTimeInterface { return new \DateTime(); }
            public function getEndDate(): ?\DateTimeInterface { return $this->endDate; }
        };
    }

    public function unsubscribe(string $subscriptionId): bool
    {
        return true;
    }

    public function handleWebhook($request): bool
    {
        return isset($request->all()['order_id']);
    }
}
