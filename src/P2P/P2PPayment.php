<?php

namespace ShamimStack\WwwPay\P2P;

use ShamimStack\WwwPay\Contracts\PaymentResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class P2PPayment
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'default_gateway' => 'paypal',
            'fee_percentage' => 2.9,
            'fixed_fee' => 0.30,
        ], $config);
    }

    public function sendMoney(array $data): PaymentResponse
    {
        if (!isset($data['amount']) || !isset($data['recipient_id'])) {
            return new PaymentResponse(
                false,
                null,
                ['errorMessage' => 'Amount and recipient_id are required.']
            );
        }

        $transactionId = 'P2P_' . Str::random(16);
        $fee = $this->calculateFee($data['amount']);

        return new PaymentResponse(
            true,
            $transactionId,
            [
                'gatewayTransactionId' => $transactionId,
                'data' => [
                    'type' => 'send',
                    'sender_id' => $data['sender_id'] ?? null,
                    'recipient_id' => $data['recipient_id'],
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? 'USD',
                    'fee' => $fee,
                    'net_amount' => $data['amount'] - $fee,
                    'status' => 'completed',
                    'completed_at' => now()->toIso8601String(),
                ],
            ]
        );
    }

    public function requestMoney(array $data): PaymentResponse
    {
        if (!isset($data['amount']) || !isset($data['sender_id'])) {
            return new PaymentResponse(
                false,
                null,
                ['errorMessage' => 'Amount and sender_id are required.']
            );
        }

        $requestId = 'REQ_' . Str::random(16);

        return new PaymentResponse(
            true,
            $requestId,
            [
                'gatewayTransactionId' => $requestId,
                'data' => [
                    'type' => 'request',
                    'sender_id' => $data['sender_id'],
                    'recipient_id' => $data['recipient_id'] ?? null,
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? 'USD',
                    'message' => $data['message'] ?? '',
                    'status' => 'pending',
                    'expires_at' => now()->addDays(7)->toIso8601String(),
                ],
            ]
        );
    }

    public function payRequest(string $requestId, array $data): PaymentResponse
    {
        $transactionId = 'P2P_' . Str::random(16);
        $fee = $this->calculateFee($data['amount'] ?? 0);

        return new PaymentResponse(
            true,
            $transactionId,
            [
                'gatewayTransactionId' => $transactionId,
                'data' => [
                    'type' => 'pay_request',
                    'request_id' => $requestId,
                    'payer_id' => $data['payer_id'] ?? '',
                    'amount' => $data['amount'] ?? 0,
                    'currency' => $data['currency'] ?? 'USD',
                    'fee' => $fee,
                    'status' => 'completed',
                ],
            ]
        );
    }

    public function splitPayment(array $data): PaymentResponse
    {
        if (!isset($data['amount']) || !isset($data['splits'])) {
            return new PaymentResponse(
                false,
                null,
                ['errorMessage' => 'Amount and splits are required.']
            );
        }

        $transactionId = 'SPLIT_' . Str::random(16);
        $totalSplit = array_sum(array_column($data['splits'], 'amount'));

        if (abs($totalSplit - $data['amount']) > 0.01) {
            return new PaymentResponse(
                false,
                null,
                ['errorMessage' => 'Split amounts do not equal total amount.']
            );
        }

        $results = [];
        foreach ($data['splits'] as $split) {
            $results[] = [
                'recipient_id' => $split['recipient_id'],
                'amount' => $split['amount'],
                'status' => 'completed',
            ];
        }

        return new PaymentResponse(
            true,
            $transactionId,
            [
                'gatewayTransactionId' => $transactionId,
                'data' => [
                    'type' => 'split',
                    'total_amount' => $data['amount'],
                    'currency' => $data['currency'] ?? 'USD',
                    'splits' => $results,
                    'status' => 'completed',
                ],
            ]
        );
    }

    public function groupPayment(array $data): PaymentResponse
    {
        if (!isset($data['total_amount']) || !isset($data['participants'])) {
            return new PaymentResponse(
                false,
                null,
                ['errorMessage' => 'Total amount and participants are required.']
            );
        }

        $groupId = 'GROUP_' . Str::random(16);
        $perPerson = $data['total_amount'] / count($data['participants']);
        $fee = $this->calculateFee($perPerson);

        return new PaymentResponse(
            true,
            $groupId,
            [
                'gatewayTransactionId' => $groupId,
                'data' => [
                    'type' => 'group',
                    'group_id' => $groupId,
                    'total_amount' => $data['total_amount'],
                    'per_person' => $perPerson,
                    'fee_per_person' => $fee,
                    'currency' => $data['currency'] ?? 'USD',
                    'participants' => count($data['participants']),
                    'status' => 'pending',
                ],
            ]
        );
    }

    public function escrowPayment(array $data): PaymentResponse
    {
        if (!isset($data['amount']) || !isset($data['recipient_id'])) {
            return new PaymentResponse(
                false,
                null,
                ['errorMessage' => 'Amount and recipient_id are required.']
            );
        }

        $escrowId = 'ESCROW_' . Str::random(16);

        return new PaymentResponse(
            true,
            $escrowId,
            [
                'gatewayTransactionId' => $escrowId,
                'data' => [
                    'type' => 'escrow',
                    'escrow_id' => $escrowId,
                    'sender_id' => $data['sender_id'] ?? null,
                    'recipient_id' => $data['recipient_id'],
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? 'USD',
                    'status' => 'held',
                    'release_conditions' => $data['conditions'] ?? null,
                    'held_at' => now()->toIso8601String(),
                ],
            ]
        );
    }

    public function releaseEscrow(string $escrowId, array $data): PaymentResponse
    {
        return new PaymentResponse(
            true,
            $escrowId,
            [
                'gatewayTransactionId' => $escrowId,
                'data' => [
                    'type' => 'escrow_release',
                    'escrow_id' => $escrowId,
                    'status' => 'released',
                    'released_at' => now()->toIso8601String(),
                ],
            ]
        );
    }

    public function cancelEscrow(string $escrowId, array $data): PaymentResponse
    {
        return new PaymentResponse(
            true,
            $escrowId,
            [
                'gatewayTransactionId' => $escrowId,
                'data' => [
                    'type' => 'escrow_cancel',
                    'escrow_id' => $escrowId,
                    'status' => 'cancelled',
                    'refund_to' => $data['sender_id'] ?? null,
                    'cancelled_at' => now()->toIso8601String(),
                ],
            ]
        );
    }

    public function bankTransfer(array $data): PaymentResponse
    {
        if (!isset($data['amount']) || !isset($data['bank_account'])) {
            return new PaymentResponse(
                false,
                null,
                ['errorMessage' => 'Amount and bank account are required.']
            );
        }

        $transferId = 'BANK_' . Str::random(16);

        return new PaymentResponse(
            true,
            $transferId,
            [
                'gatewayTransactionId' => $transferId,
                'data' => [
                    'type' => 'bank_transfer',
                    'transfer_id' => $transferId,
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? 'USD',
                    'bank_name' => $data['bank_name'] ?? '',
                    'account_holder' => $data['account_holder'] ?? '',
                    'account_last_four' => substr($data['bank_account'], -4),
                    'status' => 'processing',
                    'estimated_arrival' => now()->addDays(2)->toIso8601String(),
                ],
            ]
        );
    }

    public function mobileWallet(array $data): PaymentResponse
    {
        if (!isset($data['amount']) || !isset($data['wallet_number'])) {
            return new PaymentResponse(
                false,
                null,
                ['errorMessage' => 'Amount and wallet number are required.']
            );
        }

        $transactionId = 'WALLET_' . Str::random(16);

        return new PaymentResponse(
            true,
            $transactionId,
            [
                'gatewayTransactionId' => $transactionId,
                'data' => [
                    'type' => 'mobile_wallet',
                    'transaction_id' => $transactionId,
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? 'USD',
                    'wallet_provider' => $data['wallet_provider'] ?? 'unknown',
                    'wallet_number' => substr($data['wallet_number'], -4) . '****',
                    'status' => 'completed',
                ],
            ]
        );
    }

    public function internationalTransfer(array $data): PaymentResponse
    {
        if (!isset($data['amount']) || !isset($data['recipient_id'])) {
            return new PaymentResponse(
                false,
                null,
                ['errorMessage' => 'Amount and recipient_id are required.']
            );
        }

        $transferId = 'INTL_' . Str::random(16);
        $exchangeRate = $this->getExchangeRate(
            $data['currency'] ?? 'USD',
            $data['recipient_currency'] ?? 'EUR'
        );
        $convertedAmount = $data['amount'] * $exchangeRate;
        $transferFee = $this->calculateFee($data['amount']) * 1.5;

        return new PaymentResponse(
            true,
            $transferId,
            [
                'gatewayTransactionId' => $transferId,
                'data' => [
                    'type' => 'international',
                    'transfer_id' => $transferId,
                    'sender_id' => $data['sender_id'] ?? null,
                    'recipient_id' => $data['recipient_id'],
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? 'USD',
                    'recipient_amount' => $convertedAmount,
                    'recipient_currency' => $data['recipient_currency'] ?? 'EUR',
                    'exchange_rate' => $exchangeRate,
                    'fee' => $transferFee,
                    'estimated_delivery' => now()->addDays(5)->toIso8601String(),
                    'status' => 'processing',
                ],
            ]
        );
    }

    public function recurringPayment(array $data): PaymentResponse
    {
        if (!isset($data['amount']) || !isset($data['recipient_id'])) {
            return new PaymentResponse(
                false,
                null,
                ['errorMessage' => 'Amount and recipient_id are required.']
            );
        }

        $subscriptionId = 'RECURRING_' . Str::random(16);

        return new PaymentResponse(
            true,
            $subscriptionId,
            [
                'gatewayTransactionId' => $subscriptionId,
                'data' => [
                    'type' => 'recurring',
                    'subscription_id' => $subscriptionId,
                    'recipient_id' => $data['recipient_id'],
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? 'USD',
                    'frequency' => $data['frequency'] ?? 'monthly',
                    'start_date' => $data['start_date'] ?? now()->toIso8601String(),
                    'status' => 'active',
                ],
            ]
        );
    }

    public function refundP2P(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(
            true,
            'REFUND_' . Str::random(16),
            [
                'gatewayTransactionId' => 'REFUND_' . Str::random(16),
                'data' => [
                    'original_transaction_id' => $transactionId,
                    'refund_amount' => $amount,
                    'status' => 'refunded',
                    'refunded_at' => now()->toIso8601String(),
                ],
            ]
        );
    }

    protected function calculateFee(float $amount): float
    {
        $percentage = ($amount * $this->config['fee_percentage']) / 100;
        return round($percentage + $this->config['fixed_fee'], 2);
    }

    protected function getExchangeRate(string $from, string $to): float
    {
        $rates = [
            'USD_EUR' => 0.92,
            'USD_GBP' => 0.79,
            'USD_JPY' => 149.50,
            'USD_CAD' => 1.36,
            'USD_AUD' => 1.53,
            'EUR_USD' => 1.09,
            'GBP_USD' => 1.27,
        ];

        $key = "{$from}_{$to}";
        return $rates[$key] ?? 1.0;
    }
}
