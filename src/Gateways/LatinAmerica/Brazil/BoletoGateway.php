<?php

namespace ShamimStack\WwwPay\Gateways\LatinAmerica\Brazil;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class BoletoGateway implements PaymentGateway
{
    use HasPayments;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $transactionId = 'BOLETO_' . Str::random(12);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? $transactionId;

        $expirationDate = $data['expiration_date'] ?? date('d/m/Y', strtotime('+5 days'));
        $payerName = $data['name'] ?? '';
        $payerCpfCnpj = $data['document'] ?? '';
        $payerAddress = $data['address'] ?? [];

        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'boleto',
            'gatewayTransactionId' => $transactionId,
            'orderId' => $orderId,
            'amount' => $amount,
            'currency' => 'BRL',
            'paymentMethod' => 'BOLETO_BANCARIO',
            'expirationDate' => $expirationDate,
            'payerName' => $payerName,
            'payerDocument' => $payerCpfCnpj,
            'payerAddress' => $payerAddress,
            'returnUrl' => $data['return_url'] ?? url('/payment/callback/boleto'),
            'status' => 'PENDING',
        ]);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_BOLETO_' . Str::random(12), [
            'gateway' => 'boleto',
            'originalTransactionId' => $transactionId,
            'refundAmount' => $amount,
            'status' => 'REFUNDED',
        ]);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'boleto',
            'status' => 'CANCELLED',
        ]);
    }

    public function verify(array $data): bool
    {
        return isset($data['id']) || isset($data['order_id']);
    }

    public function getName(): string
    {
        return 'boleto';
    }

    public function supportsSubscriptions(): bool
    {
        return true;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        $subscriptionId = 'SUB_BOLETO_' . Str::random(12);
        return new \ShamimStack\WwwPay\Subscriptions\Subscription(
            $subscriptionId,
            'boleto',
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
        return isset($data['id']) && isset($data['status']);
    }
}
