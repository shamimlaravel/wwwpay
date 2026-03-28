<?php

namespace ShamimStack\WwwPay\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class PaymentEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $gateway;
    public string $transactionId;
    public float $amount;
    public string $currency;
    public array $metadata;

    public function __construct(
        string $gateway,
        string $transactionId,
        float $amount,
        string $currency,
        array $metadata = []
    ) {
        $this->gateway = $gateway;
        $this->transactionId = $transactionId;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->metadata = $metadata;
    }

    public function broadcastOn(): array
    {
        return [];
    }
}
