<?php

namespace ShamimStack\WwwPay\Gateways\LatinAmerica;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class EbanxLocalGateway implements PaymentGateway
{
    use HasPayments;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $transactionId = 'EBANXLOCAL_' . Str::random(12);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? $transactionId;

        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'ebanxlocal',
            'gatewayTransactionId' => $transactionId,
            'orderId' => $orderId,
            'amount' => $amount,
            'currency' => $data['currency'] ?? 'BRL',
            'country' => $data['country'] ?? 'BR',
            'paymentMethodCode' => $data['payment_method'] ?? '',
            'returnUrl' => $data['return_url'] ?? url('/payment/callback/ebanxlocal'),
            'status' => 'PENDING',
        ]);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_EBANXLOCAL_' . Str::random(12), [
            'gateway' => 'ebanxlocal',
            'originalTransactionId' => $transactionId,
            'refundAmount' => $amount,
            'status' => 'REFUNDED',
        ]);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, ['gateway' => 'ebanxlocal', 'status' => 'CANCELLED']);
    }

    public function verify(array $data): bool { return isset($data['hash']); }
    public function getName(): string { return 'ebanxlocal'; }
    public function supportsSubscriptions(): bool { return false; }
    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        throw new \Exception('EBANX Local does not support subscriptions');
    }
    public function unsubscribe(string $subscriptionId): bool { return false; }
    public function handleWebhook($request): bool
    {
        $data = $request->all();
        return isset($data['hash']);
    }
}
