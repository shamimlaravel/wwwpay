<?php

namespace ShamimStack\WwwPay\Events;

class SubscriptionCancelled extends PaymentEvent
{
    public string $status = 'subscription_cancelled';
    public string $subscriptionId;
    public string $planId;
    public string $customerId;
    public ?string $cancellationDate;
    public ?string $endDate;

    public function __construct(
        string $gateway,
        string $transactionId,
        float $amount,
        string $currency,
        string $subscriptionId,
        string $planId,
        string $customerId,
        ?string $cancellationDate = null,
        ?string $endDate = null,
        array $metadata = []
    ) {
        parent::__construct($gateway, $transactionId, $amount, $currency, $metadata);
        $this->subscriptionId = $subscriptionId;
        $this->planId = $planId;
        $this->customerId = $customerId;
        $this->cancellationDate = $cancellationDate;
        $this->endDate = $endDate;
    }

    public function getTags(): array
    {
        return [
            'gateway:' . $this->gateway,
            'plan:' . $this->planId,
            'type:cancellation',
        ];
    }
}
