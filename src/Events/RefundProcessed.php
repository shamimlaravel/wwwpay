<?php

namespace ShamimStack\WwwPay\Events;

class RefundProcessed extends PaymentEvent
{
    public string $status = 'refunded';
    public string $originalTransactionId;
    public float $refundAmount;
    public ?string $reason;

    public function __construct(
        string $gateway,
        string $transactionId,
        float $amount,
        string $currency,
        string $originalTransactionId,
        float $refundAmount,
        ?string $reason = null,
        array $metadata = []
    ) {
        parent::__construct($gateway, $transactionId, $amount, $currency, $metadata);
        $this->originalTransactionId = $originalTransactionId;
        $this->refundAmount = $refundAmount;
        $this->reason = $reason;
    }

    public function getTags(): array
    {
        return [
            'gateway:' . $this->gateway,
            'type:refund',
        ];
    }
}
