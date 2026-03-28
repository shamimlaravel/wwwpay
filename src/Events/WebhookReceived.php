<?php

namespace ShamimStack\WwwPay\Events;

class WebhookReceived extends PaymentEvent
{
    public string $status = 'webhook_received';
    public string $eventType;
    public array $payload;
    public bool $verified;

    public function __construct(
        string $gateway,
        string $transactionId,
        float $amount,
        string $currency,
        string $eventType,
        array $payload,
        bool $verified = true,
        array $metadata = []
    ) {
        parent::__construct($gateway, $transactionId, $amount, $currency, $metadata);
        $this->eventType = $eventType;
        $this->payload = $payload;
        $this->verified = $verified;
    }

    public function getTags(): array
    {
        return [
            'gateway:' . $this->gateway,
            'event:' . $this->eventType,
            'verified:' . ($this->verified ? 'true' : 'false'),
        ];
    }
}
