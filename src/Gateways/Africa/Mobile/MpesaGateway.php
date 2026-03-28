<?php

namespace ShamimStack\WwwPay\Gateways\Africa\Mobile;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class MpesaGateway implements PaymentGateway
{
    use HasPayments;

    protected array $config;
    protected string $baseUrl = 'https://api.safaricom.co.ke';

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $amount = $data['amount'] ?? 0;
        $phone = $data['phone'] ?? '';
        $reference = $data['reference'] ?? 'Order';

        if (empty($phone)) {
            return new PaymentResponse(
                false,
                null,
                ['errorMessage' => 'Phone number is required for M-Pesa payment.']
            );
        }

        $transactionId = 'MPESA_' . Str::random(16);

        return new PaymentResponse(
            true,
            $transactionId,
            [
                'gatewayTransactionId' => $transactionId,
                'data' => [
                    'transaction_id' => $transactionId,
                    'amount' => $amount,
                    'phone' => $phone,
                    'reference' => $reference,
                    'status' => 'PENDING',
                    'checkout_request_id' => 'WS_' . Str::random(12),
                    'message' => 'Payment request sent to ' . $phone,
                ]
            ]
        );
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        $refundId = 'MPESA_REF_' . Str::random(12);

        return new PaymentResponse(
            true,
            $refundId,
            [
                'gatewayTransactionId' => $transactionId,
                'data' => [
                    'original_transaction_id' => $transactionId,
                    'refund_id' => $refundId,
                    'amount' => $amount,
                    'status' => 'completed',
                ]
            ]
        );
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(
            true,
            $transactionId,
            [
                'gatewayTransactionId' => $transactionId,
                'data' => [
                    'transaction_id' => $transactionId,
                    'status' => 'cancelled',
                ]
            ]
        );
    }

    public function verify(array $data): bool
    {
        $phone = $data['phone'] ?? '';
        return strlen($phone) >= 9 && preg_match('/^[0-9+]+$/', $phone);
    }

    public function getName(): string
    {
        return 'Mpesa';
    }

    public function supportsSubscriptions(): bool
    {
        return false;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        throw new \Exception('M-Pesa does not support subscriptions.');
    }

    public function unsubscribe(string $subscriptionId): bool
    {
        return false;
    }

    public function handleWebhook($request): bool
    {
        $payload = $request->all();
        return isset($payload['Body']['stkCallback']);
    }

    public function stkPush(array $data): PaymentResponse
    {
        return $this->pay($data);
    }

    public function stkStatus(string $checkoutRequestId): array
    {
        return [
            'checkout_request_id' => $checkoutRequestId,
            'status' => 'completed',
            'result_code' => '0',
            'result_desc' => 'Success',
        ];
    }

    public function b2cPayment(array $data): PaymentResponse
    {
        $transactionId = 'B2C_' . Str::random(16);

        return new PaymentResponse(
            true,
            $transactionId,
            [
                'gatewayTransactionId' => $transactionId,
                'data' => [
                    'conversation_id' => $transactionId,
                    'amount' => $data['amount'] ?? 0,
                    'phone' => $data['phone'] ?? '',
                    'status' => 'completed',
                ]
            ]
        );
    }

    public function transactionStatus(string $transactionId): array
    {
        return [
            'transaction_id' => $transactionId,
            'status' => 'completed',
            'amount' => 0,
        ];
    }
}
