<?php

namespace ShamimStack\WwwPay\Gateways\LatinAmerica\Argentina;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class MercadoPagoARGateway implements PaymentGateway
{
    use HasPayments;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $transactionId = 'MP_AR_' . Str::random(12);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? $transactionId;

        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'mercadopago_ar',
            'gatewayTransactionId' => $transactionId,
            'orderId' => $orderId,
            'amount' => $amount,
            'currency' => 'ARS',
            'payerEmail' => $data['email'] ?? '',
            'description' => $data['description'] ?? 'MercadoPago Argentina Payment',
            'returnUrl' => $data['return_url'] ?? url('/payment/callback/mercadopago_ar'),
            'status' => 'PENDING',
        ]);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_MP_AR_' . Str::random(12), [
            'gateway' => 'mercadopago_ar',
            'originalTransactionId' => $transactionId,
            'refundAmount' => $amount,
            'status' => 'REFUNDED',
        ]);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'mercadopago_ar',
            'status' => 'CANCELLED',
        ]);
    }

    public function verify(array $data): bool
    {
        return isset($data['id']) || isset($data['order_id']);
    }

    public function getName(): string
    {
        return 'mercadopago_ar';
    }

    public function supportsSubscriptions(): bool
    {
        return true;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        $subscriptionId = 'SUB_MP_AR_' . Str::random(12);
        return new \ShamimStack\WwwPay\Subscriptions\Subscription(
            $subscriptionId,
            'mercadopago_ar',
            $data['amount'] ?? 0,
            'monthly',
            'active'
        );
    }

    public function unsubscribe(string $subscriptionId): bool
    {
        return true;
    }

    public function handleWebhook($request): bool
    {
        $data = $request->all();
        return isset($data['id']) && isset($data['type']);
    }
}
