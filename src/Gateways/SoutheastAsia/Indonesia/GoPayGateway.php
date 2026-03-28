<?php

namespace ShamimStack\WwwPay\Gateways\SoutheastAsia\Indonesia;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class GoPayGateway implements PaymentGateway
{
    use HasPayments;

    protected array $config;
    protected string $baseUrl = 'https://api.gojekapi.com';

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $amount = $data['amount'] ?? 0;
        $phone = $data['phone'] ?? '';
        $orderId = 'GOPAY_' . Str::random(12);

        return new PaymentResponse(true, $orderId, [
            'gatewayTransactionId' => $orderId,
            'data' => [
                'order_id' => $orderId,
                'amount' => $amount,
                'currency' => 'IDR',
                'phone' => $phone,
                'status' => 'PENDING',
                'expire_time' => date('c', strtotime('+15 minutes')),
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
        return !empty($data['phone']) || !empty($data['order_id']);
    }

    public function getName(): string
    {
        return 'gopay';
    }

    public function supportsSubscriptions(): bool
    {
        return true;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        $subscriptionId = 'GOPAY_SUB_' . Str::random(12);
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
