<?php

namespace ShamimStack\AllInOnePayment\Contracts;

use Illuminate\Http\Request;

interface PaymentGateway
{
    public function pay(array $data): PaymentResponse;

    public function refund(string $transactionId, float $amount = null): PaymentResponse;

    public function cancel(string $transactionId): PaymentResponse;

    public function subscribe(array $data): Subscription;

    public function handleWebhook(Request $request): bool;

    public function getName(): string;
}