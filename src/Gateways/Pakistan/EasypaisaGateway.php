<?php

namespace ShamimStack\AllInOnePayment\Gateways\Pakistan;

use ShamimStack\AllInOnePayment\Contracts\PaymentGateway;
use ShamimStack\AllInOnePayment\Contracts\PaymentResponse;
use ShamimStack\AllInOnePayment\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class EasypaisaGateway implements PaymentGateway
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
            if (!isset($data['amount']) || !isset($data['currency']) || !isset($data['mobile_number'])) {
                throw new PaymentException('Amount, currency, and mobile_number are required for Easypaisa payment.');
            }

            // Validate currency (Easypaisa primarily processes PKR)
            if (strtoupper($data['currency']) !== 'PKR') {
                throw new PaymentException('Easypaisa gateway primarily supports PKR currency.');
            }

            // Prepare Easypaisa payment data
            // Note: Easypaisa API implementation varies. This is a simplified version.
            // In practice, you would use their official SDK or API endpoints.
            
            $paymentData = [
                'amount' => number_format($data['amount'], 2, '.', ''),
                'currency' => 'PKR',
                'mobile_number' => $data['mobile_number'],
                'merchant_id' => $this->config['merchant_id'],
                'merchant_password' => $this->config['merchant_password'],
                'reference_id' => $data['reference_id'] ?? uniqid(),
                'description' => $data['description'] ?? 'Easypaisa Payment',
                'callback_url' => $data['callback_url'] ?? url('/payment/callback/easypaisa'),
                'expiry_time' => date('Y-m-d H:i:s', strtotime('+30 minutes')),
            ];

            // Make API request to Easypaisa
            $response = Http::post($this->getEasypaisaEndpoint(), $paymentData);

            if ($response->successful()) {
                $result = $response->json();

                // Check if payment was initiated successfully
                if (isset($result['status']) && $result['status'] === 'success') {
                    return new PaymentResponse(
                        false, // Easypaisa typically requires user to complete payment via their app/USSD
                        $result['transaction_id'] ?? $data['reference_id'],
                        [
                            'gatewayTransactionId' => $result['transaction_id'] ?? $data['reference_id'],
                            'data' => [
                                'status' => $result['status'],
                                'transaction_id' => $result['transaction_id'] ?? $data['reference_id'],
                                'amount' => $result['amount'] ?? $data['amount'],
                                'currency' => $result['currency'] ?? 'PKR',
                                'mobile_number' => $result['mobile_number'] ?? $data['mobile_number'],
                                'reference_id' => $result['reference_id'] ?? $data['reference_id'],
                                'expires_at' => $result['expires_at'] ?? null,
                            ],
                        ]
                    );
                }

                // Payment initiation failed
                return new PaymentResponse(
                    false,
                    $result['transaction_id'] ?? $data['reference_id'],
                    [
                        'errorMessage' => $result['message'] ?? 'Payment initiation failed',
                        'gatewayTransactionId' => $result['transaction_id'] ?? $data['reference_id'],
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
                    'errorMessage' => 'Failed to connect to Easypaisa payment gateway.',
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
                'transaction_id' => $transactionId,
                'merchant_id' => $this->config['merchant_id'],
                'merchant_password' => $this->config['merchant_password'],
                'amount' => $amount !== null ? number_format($amount, 2, '.', '') : null,
                'currency' => 'PKR',
                'reference_id' => 'REF_' . uniqid(),
                'description' => 'Refund for transaction ' . $transactionId,
            ];

            // Remove null values
            $refundData = array_filter($refundData);

            // Make API request to Easypaisa refund endpoint
            $response = Http::post($this->getEasypaisaRefundEndpoint(), $refundData);

            if ($response->successful()) {
                $result = $response->json();

                // Check if refund was successful
                if (isset($result['status']) && $result['status'] === 'success') {
                    return new PaymentResponse(
                        true,
                        $result['refund_transaction_id'] ?? $transactionId . '_REF',
                        [
                            'gatewayTransactionId' => $result['refund_transaction_id'] ?? $transactionId . '_REF',
                            'data' => [
                                'status' => $result['status'],
                                'message' => $result['message'] ?? 'Refund processed successfully',
                                'amount' => $result['amount'] ?? ($amount ?? 0),
                                'currency' => $result['currency'] ?? 'PKR',
                                'reference_id' => $result['reference_id'] ?? $transactionId,
                            ],
                        ]
                    );
                }

                // Refund failed
                return new PaymentResponse(
                    false,
                    $result['transaction_id'] ?? $transactionId . '_REF',
                    [
                        'errorMessage' => $result['message'] ?? 'Refund failed',
                        'gatewayTransactionId' => $result['transaction_id'] ?? $transactionId . '_REF',
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
                    'errorMessage' => 'Failed to connect to Easypaisa payment gateway for refund.',
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
     * Note: Easypaisa doesn't typically support direct cancellation of processed payments.
     * Cancellation is usually done via refund for settled transactions.
     *
     * @param string $transactionId Transaction ID to cancel
     * @return PaymentResponse
     */
    public function cancel(string $transactionId): PaymentResponse
    {
        try {
            return new PaymentResponse(
                false,
                $transactionId,
                [
                    'errorMessage' => 'Direct cancellation is not supported for Easypaisa. Please use refund for settled transactions.',
                    'gatewayTransactionId' => $transactionId,
                    'data' => [
                        'suggestion' => 'Use refund method instead of cancel for Easypaisa gateway.',
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
     * Note: Easypaisa gateway primarily handles one-time payments.
     * Recurring payments would need custom implementation.
     *
     * @param array $data Subscription data
     * @return Subscription
     */
    public function subscribe(array $data): \ShamimStack\AllInOnePayment\Models\Subscription
    {
        // Easypaisa doesn't natively support subscriptions through their standard API.
        // For recurring payments, merchants would need to implement custom solutions.
        return new \ShamimStack\AllInOnePayment\Models\Subscription([
            'gateway' => 'easypaisa',
            'gateway_subscription_id' => 'sub_easypaisa_' . uniqid(),
            'status' => 'active',
            'plan_id' => $data['plan_id'] ?? null,
            'customer_id' => $data['mobile_number'] ?? null,
            'start_date' => now(),
            'end_date' => null,
            'data' => [
                'note' => 'Easypaisa gateway does not natively support subscriptions. Recurring payments require custom implementation.',
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
            
            // Easypaisa sends webhook data as form parameters or JSON
            $input = $request->all();
            
            if (empty($input)) {
                $input = json_decode($payload, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return false;
                }
            }
            
            # Verify the webhook signature if needed
            # Implementation depends on Easypaisa's webhook security mechanism
            
            # Process the webhook data
            # Example: check payment status, update database, etc.
            
            # For now, we'll just return true to acknowledge receipt
            return true;
        } catch (\Exception $e) {
            # Log the error in a real implementation
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
        return 'easypaisa';
    }

    /**
     * Get the Easypaisa payment endpoint
     *
     * @return string
     */
    protected function getEasypaisaEndpoint(): string
    {
        // This is a placeholder - replace with actual Easypaisa endpoint
        if ($this->config['mode'] === 'live') {
            return 'https://api.easypaisa.com.pk/epayment/v1/payment';
        } else {
            return 'https://api.sandbox.easypaisa.com.pk/epayment/v1/payment';
        }
    }

    /**
     * Get the Easypaisa refund endpoint
     *
     * @return string
     */
    protected function getEasypaisaRefundEndpoint(): string
    {
        // This is a placeholder - replace with actual Easypaisa refund endpoint
        if ($this->config['mode'] === 'live') {
            return 'https://api.easypaisa.com.pk/epayment/v1/refund';
        } else {
            return 'https://api.sandbox.easypaisa.com.pk/epayment/v1/refund';
        }
    }
}