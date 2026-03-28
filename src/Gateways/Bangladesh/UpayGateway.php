<?php

namespace ShamimStack\WwwPay\Gateways\Bangladesh;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class UpayGateway implements PaymentGateway
{
    use HasPayments;

    protected array $config;
    protected string $baseUrl = 'https://api.upaybd.com';

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $amount = $data['amount'] ?? 0;
        $msisdn = $data['msisdn'] ?? '';
        $merchantTransId = 'UP_' . Str::random(12);

        if (empty($msisdn)) {
            return new PaymentResponse(false, null, ['errorMessage' => 'Mobile number is required']);
        }

        $transactionId = 'UPAY_' . Str::random(16);

        return new PaymentResponse(
            true,
            $transactionId,
            [
                'gatewayTransactionId' => $transactionId,
                'data' => [
                    'transaction_id' => $transactionId,
                    'merchant_trans_id' => $merchantTransId,
                    'msisdn' => $msisdn,
                    'amount' => $amount,
                    'currency' => 'BDT',
                    'status' => 'PENDING',
                ]
            ]
        );
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_' . Str::random(12), [
            'original_transaction_id' => $transactionId,
            'status' => 'refund_initiated'
        ]);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, ['status' => 'cancelled']);
    }

    public function verify(array $data): bool
    {
        return !empty($data['msisdn']) && strlen($data['msisdn']) >= 11;
    }

    public function getName(): string
    {
        return 'upay';
    }

    public function supportsSubscriptions(): bool
    {
        return false;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        throw new \Exception('Upay does not support subscriptions');
    }

    public function unsubscribe(string $subscriptionId): bool
    {
        return false;
    }

    public function handleWebhook($request): bool
    {
        return isset($request->all()['status']);
    }
}
