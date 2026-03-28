<?php

namespace ShamimStack\WwwPay\Gateways\LatinAmerica\Brazil;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class PIXGateway implements PaymentGateway
{
    use HasPayments;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $transactionId = 'PIX_' . Str::random(12);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? $transactionId;

        $brcodeData = [
            'merchantCategoryCode' => '0000',
            'currency' => '986',
            'countryCode' => 'BR',
            'merchantName' => $this->config['merchant_name'] ?? 'Merchant',
            'merchantCity' => $this->config['merchant_city'] ?? 'City',
            'transactionId' => $orderId,
            'amount' => number_format($amount, 2, '.', ''),
        ];

        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'pix',
            'gatewayTransactionId' => $transactionId,
            'orderId' => $orderId,
            'amount' => $amount,
            'currency' => 'BRL',
            'paymentMethod' => 'PIX',
            'brcodeData' => $brcodeData,
            'returnUrl' => $data['return_url'] ?? url('/payment/callback/pix'),
            'status' => 'PENDING',
        ]);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_PIX_' . Str::random(12), [
            'gateway' => 'pix',
            'originalTransactionId' => $transactionId,
            'refundAmount' => $amount,
            'status' => 'REFUNDED',
        ]);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'pix',
            'status' => 'CANCELLED',
        ]);
    }

    public function verify(array $data): bool
    {
        return isset($data['txid']) || isset($data['order_id']);
    }

    public function getName(): string
    {
        return 'pix';
    }

    public function supportsSubscriptions(): bool
    {
        return true;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        $subscriptionId = 'SUB_PIX_' . Str::random(12);
        return new \ShamimStack\WwwPay\Subscriptions\Subscription(
            $subscriptionId,
            'pix',
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
        return isset($data['txid']) || isset($data['endToEndId']);
    }
}
