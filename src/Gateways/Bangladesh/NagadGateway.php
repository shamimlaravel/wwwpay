<?php

namespace ShamimStack\AllInOnePayment\Gateways\Bangladesh;

use ShamimStack\AllInOnePayment\Contracts\PaymentGateway;
use ShamimStack\AllInOnePayment\Contracts\PaymentResponse;
use ShamimStack\AllInOnePayment\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class NagadGateway implements PaymentGateway
{
    /**
     * @var array Configuration settings
     */
    protected $config;

    /**
     * Create a new gateway instance.
     *
     * @param  array  $config
     * @return void
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Process a payment
     *
     * @param array $data Payment data
     * @return PaymentResponse
     */
    public function pay(array $data): PaymentResponse
    {
        try {
            // Validate required fields
            if (!isset($data['amount']) || !isset($data['currency']) || !isset($data['customer_phone'])) {
                throw new PaymentException('Amount, currency, and customer_phone are required for Nagad payment.');
            }

            // Validate currency (Nagad primarily processes BDT)
            if (strtoupper($data['currency']) !== 'BDT') {
                throw new PaymentException('Nagad gateway primarily supports BDT currency.');
            }

            // Prepare payment data for Nagad API
            $paymentData = [
                'merchant_id' => $this->config['merchant_id'],
                'merchant_password' => $this->config['merchant_password'],
                'amount' => number_format($data['amount'], 2, '.', ''),
                'currency' => 'BDT',
                'reference_id' => $data['reference_id'] ?? uniqid(),
                'basket' => json_encode([
                    'username' => $data['customer_name'] ?? 'Customer',
                    'msisdn' => $data['customer_phone'],
                    'amount' => $data['amount'],
                    'currency' => 'BDT',
                    'description' => $data['description'] ?? 'Payment',
                ]),
                'callback_url' => $data['callback_url'] ?? url('/payment/callback/nagad'),
                'cancel_url' => $data['cancel_url'] ?? url('/payment/cancel'),
                'timeout' => $data['timeout'] ?? 30, // minutes
            ];

            // Make API request to Nagad
            $response = Http::post($this->getPaymentEndpoint(), $paymentData);

            if ($response->successful()) {
                $result = $response->json();

                // Check if payment was initiated successfully
                if (isset($result['status']) && $result['status'] === 'success') {
                    return new PaymentResponse(
                        false, // Nagad typically requires user to complete payment via their app
                        $result['payment_session_id'] ?? $result['reference_id'],
                        [
                            'gatewayTransactionId' => $result['payment_session_id'] ?? $result['reference_id'],
                            'data' => [
                                'status' => $result['status'],
                                'payment_url' => $result['payment_url'] ?? null, // URL for user to complete payment
                                'reference_id' => $result['reference_id'],
                                'amount' => $result['amount'] ?? $data['amount'],
                                'currency' => $result['currency'] ?? 'BDT',
                                'expires_at' => $result['expires_at'] ?? null,
                            ],
                        ]
                    );
                }

                // Payment initiation failed
                return new PaymentResponse(
                    false,
                    $result['reference_id'] ?? null,
                    [
                        'errorMessage' => $result['message'] ?? 'Payment initiation failed',
                        'gatewayTransactionId' => $result['reference_id'] ?? null,
                        'data' => [
                            'status' => $result['status'] ?? 'failed',
                            'message' => $result['message'] ?? null,
                        ],
                    ]
                );
            }

            // HTTP request failed
            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => 'Failed to connect to Nagad payment gateway.',
                ]
            );
        } catch (\Exception $e) {
            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * Refund a payment
     *
     * @param string $transactionId Transaction ID to refund
     * @param float|null $amount Amount to refund (null for full refund)
     * @return PaymentResponse
     */
    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        try {
            // Validate transaction ID
            if (empty($transactionId)) {
                throw new PaymentException('Transaction ID is required for refund.');
            }

            // Prepare refund request data
            $refundData = [
                'merchant_id' => $this->config['merchant_id'],
                'merchant_password' => $this->config['merchant_password'],
                'reference_id' => $transactionId,
                'amount' => $amount !== null ? number_format($amount, 2, '.', '') : null,
                'currency' => 'BDT',
                'refund_reference' => 'REF_' . uniqid(),
                'description' => 'Refund for transaction ' . $transactionId,
            ];

            // Remove null values
            $refundData = array_filter($refundData);

            // Make API request to Nagad refund endpoint
            $response = Http::post($this->getRefundEndpoint(), $refundData);

            if ($response->successful()) {
                $result = $response->json();

                // Check if refund was successful
                if (isset($result['status']) && $result['status'] === 'success') {
                    return new PaymentResponse(
                        true,
                        $result['refund_reference'] ?? $transactionId . '_REF',
                        [
                            'gatewayTransactionId' => $result['refund_reference'] ?? $transactionId . '_REF',
                            'data' => [
                                'status' => $result['status'],
                                'message' => $result['message'] ?? 'Refund processed successfully',
                                'amount' => $result['amount'] ?? ($amount ?? 0),
                                'currency' => $result['currency'] ?? 'BDT',
                                'reference_id' => $result['reference_id'] ?? $transactionId,
                            ],
                        ]
                    );
                }

                // Refund failed
                return new PaymentResponse(
                    false,
                    $result['reference_id'] ?? $transactionId . '_REF',
                    [
                        'errorMessage' => $result['message'] ?? 'Refund failed',
                        'gatewayTransactionId' => $result['reference_id'] ?? $transactionId . '_REF',
                        'data' => [
                            'status' => $result['status'] ?? 'failed',
                            'message' => $result['message'] ?? null,
                        ],
                    ]
                );
            }

            // HTTP request failed
            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => 'Failed to connect to Nagad payment gateway for refund.',
                ]
            );
        } catch (\Exception $e) {
            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * Cancel a payment
     *
     * Note: Nagad doesn't typically support direct cancellation of processed payments.
     * Cancellation is usually done via refund for settled transactions.
     *
     * @param string $transactionId Transaction ID to cancel
     * @return PaymentResponse
     */
    public function cancel(string $transactionId): PaymentResponse
    {
        try {
            // For Nagad, we'll check if we can cancel (void) the transaction
            // Otherwise, we'll suggest a refund for settled transactions
            
            // First, check transaction status (simplified)
            return new PaymentResponse(
                false,
                $transactionId,
                [
                    'errorMessage' => 'Direct cancellation is not supported for Nagad. Please use refund for settled transactions.',
                    'gatewayTransactionId' => $transactionId,
                    'data' => [
                        'suggestion' => 'Use refund method instead of cancel for Nagad gateway.',
                    ],
                ]
            );
        } catch (\Exception $e) {
            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * Create a subscription
     *
     * Note: Nagad gateway primarily handles one-time payments.
     * Recurring payments would need to be implemented by storing customer details securely
     * and initiating new transactions on schedule.
     *
     * @param array $data Subscription data
     * @return Subscription
     */
    public function subscribe(array $data): \ShamimStack\AllInOnePayment\Models\Subscription
    {
        // Nagad doesn't natively support subscriptions through their standard API.
        // For recurring payments, merchants would need to implement custom solutions.
        return new \ShamimStack\AllInOnePayment\Models\Subscription([
            'gateway' => 'nagad',
            'gateway_subscription_id' => 'sub_nagad_' . uniqid(),
            'status' => 'active',
            'plan_id' => $data['plan_id'] ?? null,
            'customer_id' => $data['customer_phone'] ?? null,
            'start_date' => now(),
            'end_date' => null,
            'data' => [
                'note' => 'Nagad gateway does not natively support subscriptions. Recurring payments require custom implementation.',
            ],
        ]);
    }

    /**
     * Handle webhook from payment gateway
     *
     * @param Request $request HTTP request
     * @return bool Success status
     */
    public function handleWebhook(\Illuminate\Http\Request $request): bool
    {
        try {
            // Get the payload
            $payload = $request->getContent();
            
            // Nagad sends webhook data as form parameters or JSON
            $input = $request->all();
            
            if (empty($input)) {
                $input = json_decode($payload, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return false;
                }
            }
            
            // Verify the webhook signature if needed
            // Implementation depends on Nagad's webhook security mechanism
            
            // Process the webhook data
            // Example: check payment status, update database, etc.
            
            // For now, we'll just return true to acknowledge receipt
            return true;
        } catch (\Exception $e) {
            // Log the error in a real implementation
            return false;
        }
    }

    /**
     * Get gateway name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'nagad';
    }

    /**
     * Get the Nagad payment endpoint
     *
     * @return string
     */
    protected function getPaymentEndpoint(): string
    {
        // This is a placeholder - replace with actual Nagad endpoint
        if ($this->config['mode'] === 'live') {
            return 'https://api.nagad.com.bd/api/v1/payment/request';
        } else {
            return 'https://api.sandbox.nagad.com.bd/api/v1/payment/request';
        }
    }

    /**
     * Get the Nagad refund endpoint
     *
     * @return string
     */
    protected function getRefundEndpoint(): string
    {
        // This is a placeholder - replace with actual Nagad refund endpoint
        if ($this->config['mode'] === 'live') {
            return 'https://api.nagad.com.bd/api/v1/payment/refund';
        } else {
            return 'https://api.sandbox.nagad.com.bd/api/v1/payment/refund';
        }
    }
}