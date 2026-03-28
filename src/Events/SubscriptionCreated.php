<?php

namespace ShamimStack\WwwPay\Events;

class SubscriptionCreated extends PaymentEvent
{
    public string $status = 'subscription_created';
    public string $subscriptionId;
    public string $planId;
    public string $customerId;
    public ?string $customerEmail;
    public ?string $nextBillingDate;

    public function __construct(
        string $gateway,
        string $transactionId,
        float $amount,
        string $currency,
        string $subscriptionId,
        string $planId,
        string $customerId,
        ?string $customerEmail = null,
        ?string $nextBillingDate = null,
        array $metadata = []
    ) {
        parent::__construct($gateway, $transactionId, $amount, $currency, $metadata);
        $this->subscriptionId = $subscriptionId;
        $this->planId = $planId;
        $this->customerId = $customerId;
        $this->customerEmail = $customerEmail;
        $this->nextBillingDate = $nextBillingDate;
    }

    public function getTags(): array
    {
        return [
            'gateway:' . $this->gateway,
            'plan:' . $this->planId,
            'type:subscription',
        ];
    }
}
