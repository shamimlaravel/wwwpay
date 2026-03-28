<?php

namespace ShamimStack\WwwPay\Events;

class PaymentFailed extends PaymentEvent
{
    public string $status = 'failed';
    public string $errorCode;
    public string $errorMessage;
    public ?string $customerEmail;

    public function __construct(
        string $gateway,
        string $transactionId,
        float $amount,
        string $currency,
        string $errorCode,
        string $errorMessage,
        ?string $customerEmail = null,
        array $metadata = []
    ) {
        parent::__construct($gateway, $transactionId, $amount, $currency, $metadata);
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
        $this->customerEmail = $customerEmail;
    }

    public function getTags(): array
    {
        return [
            'gateway:' . $this->gateway,
            'error:' . $this->errorCode,
        ];
    }
}
