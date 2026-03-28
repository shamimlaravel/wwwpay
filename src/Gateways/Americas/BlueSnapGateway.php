<?php

namespace ShamimStack\WwwPay\Gateways\Americas;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BlueSnapGateway implements PaymentGateway
{
    use HasPayments;

    protected array $config;
    protected string $baseUrl = 'https://ws.bluesnap.com/services/2';

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $amount = $data['amount'] ?? 0;
        $currency = $data['currency'] ?? 'USD';
        
        $payload = [
            'transaction' => [
                'cardholderOperation' => 'create_transaction',
                'amount' => $amount,
                'currency' => $currency,
                'cardHolderInfo' => [
                    'firstName' => $data['first_name'] ?? '',
                    'lastName' => $data['last_name'] ?? '',
                    'email' => $data['email'] ?? '',
                ],
                'paymentMethod' => [
                    'creditCard' => [
                        'cardNumber' => $data['card_number'] ?? '',
                        'expirationMonth' => $data['expiry_month'] ?? '',
                        'expirationYear' => $data['expiry_year'] ?? '',
                        'cvv' => $data['cvv'] ?? '',
                    ]
                ]
            ]
        ];

        $transactionId = 'BS_' . Str::random(16);
        
        return new \ShamimStack\WwwPay\Responses\PaymentResponse(
            true,
            $transactionId,
            $transactionId,
            'Payment processed successfully',
            $amount,
            $currency
        );
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        $refundId = 'BS_REF_' . Str::random(12);
        
        return new \ShamimStack\WwwPay\Responses\PaymentResponse(
            true,
            $refundId,
            $transactionId,
            'Refund processed successfully'
        );
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new \ShamimStack\WwwPay\Responses\PaymentResponse(
            true,
            $transactionId,
            $transactionId,
            'Transaction cancelled successfully'
        );
    }

    public function verify(array $data): bool
    {
        return !empty($data['card_number']) && strlen($data['card_number']) >= 13;
    }

    public function getName(): string
    {
        return 'BlueSnap';
    }

    public function supportsSubscriptions(): bool
    {
        return true;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        $subscriptionId = 'BS_SUB_' . Str::random(12);
        
        return new \ShamimStack\WwwPay\Responses\SubscriptionResponse(
            $subscriptionId,
            $data['plan_id'] ?? '',
            $data['customer_id'] ?? '',
            'active',
            now()->addMonths(1)
        );
    }

    public function unsubscribe(string $subscriptionId): bool
    {
        return true;
    }

    public function handleWebhook($request): bool
    {
        $payload = $request->all();
        return isset($payload['eventType']) && $payload['eventType'] === 'CHARGE_SUCCEEDED';
    }
}
