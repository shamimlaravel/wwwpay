<?php

namespace ShamimStack\WwwPay\Events;

class PaymentSuccessful extends PaymentEvent
{
    public string $status = 'successful';
    public ?string $customerEmail;
    public ?string $paymentMethod;

    public function __construct(
        string $gateway,
        string $transactionId,
        float $amount,
        string $currency,
        ?string $customerEmail = null,
        ?string $paymentMethod = null,
        array $metadata = []
    ) {
        parent::__construct($gateway, $transactionId, $amount, $currency, $metadata);
        $this->customerEmail = $customerEmail;
        $this->paymentMethod = $paymentMethod;
    }

    public function getTags(): array
    {
        return [
            'gateway:' . $this->gateway,
            'currency:' . $this->currency,
        ];
    }
}
