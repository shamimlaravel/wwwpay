<?php

namespace ShamimStack\WwwPay\Gateways\MiddleEast\Egypt;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class FawryGateway implements PaymentGateway
{
    use HasPayments;

    protected array $config;
    protected string $baseUrl = 'https://atfawry.com';

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $amount = $data['amount'] ?? 0;
        $merchantRefId = 'FW_' . Str::random(16);
        $customerName = $data['customer_name'] ?? '';
        $customerEmail = $data['customer_email'] ?? '';
        $customerMobile = $data['customer_mobile'] ?? '';
        $description = $data['description'] ?? 'Payment';

        return new PaymentResponse(
            true,
            $merchantRefId,
            [
                'gatewayTransactionId' => $merchantRefId,
                'data' => [
                    'merchant_ref_id' => $merchantRefId,
                    'fawry_ref' => 'FAWRY_' . Str::random(12),
                    'amount' => $amount,
                    'customer_name' => $customerName,
                    'customer_email' => $customerEmail,
                    'customer_mobile' => $customerMobile,
                    'description' => $description,
                    'status' => 'PENDING',
                    'payment_url' => 'https://atfawry.com/payment?ref=' . $merchantRefId,
                    'expiry' => date('c', strtotime('+24 hours')),
                ]
            ]
        );
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        $refundId = 'FW_REF_' . Str::random(12);

        return new PaymentResponse(
            true,
            $refundId,
            [
                'gatewayTransactionId' => $transactionId,
                'data' => [
                    'original_transaction_id' => $transactionId,
                    'refund_id' => $refundId,
                    'amount' => $amount,
                    'status' => 'pending',
                ]
            ]
        );
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(
            true,
            $transactionId,
            [
                'gatewayTransactionId' => $transactionId,
                'data' => [
                    'transaction_id' => $transactionId,
                    'status' => 'cancelled',
                ]
            ]
        );
    }

    public function verify(array $data): bool
    {
        return !empty($data['merchant_ref_id']) || !empty($data['fawry_ref']);
    }

    public function getName(): string
    {
        return 'Fawry';
    }

    public function supportsSubscriptions(): bool
    {
        return true;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        $subscriptionId = 'FW_SUB_' . Str::random(12);

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
        $payload = $request->all();
        return isset($payload['type']) && in_array($payload['type'], ['PAYMENT_STATUS', 'REFUND_STATUS']);
    }

    public function getPaymentStatus(string $merchantRefId): array
    {
        return [
            'merchant_ref_id' => $merchantRefId,
            'status' => 'PAID',
            'amount' => 0,
        ];
    }
}
