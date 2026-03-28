<?php

namespace ShamimStack\WwwPay\Gateways\Bangladesh;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class RocketGateway implements PaymentGateway
{
    use HasPayments;

    protected array $config;
    protected string $baseUrl = 'https://api.dutchbanglabank.com';

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $amount = $data['amount'] ?? 0;
        $accountNo = $data['account_no'] ?? '';
        $referenceId = $data['reference_id'] ?? 'REF_' . Str::random(10);

        if (empty($accountNo)) {
            return new PaymentResponse(false, null, ['errorMessage' => 'Account number is required']);
        }

        $transactionId = 'RK_' . Str::random(16);

        return new PaymentResponse(
            true,
            $transactionId,
            [
                'gatewayTransactionId' => $transactionId,
                'data' => [
                    'transaction_id' => $transactionId,
                    'account_no' => $accountNo,
                    'amount' => $amount,
                    'currency' => 'BDT',
                    'reference_id' => $referenceId,
                    'status' => 'PENDING',
                    'message' => 'Rocket payment initiated. Complete payment via Rocket app.',
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
        return !empty($data['account_no']) && strlen($data['account_no']) >= 11;
    }

    public function getName(): string
    {
        return 'rocket';
    }

    public function supportsSubscriptions(): bool
    {
        return false;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        throw new \Exception('Rocket does not support subscriptions');
    }

    public function unsubscribe(string $subscriptionId): bool
    {
        return false;
    }

    public function handleWebhook($request): bool
    {
        $payload = $request->all();
        return isset($payload['status']);
    }
}
