<?php

namespace ShamimStack\WwwPay\Gateways\AsiaPacific;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PayPayGateway implements PaymentGateway
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
            if (!isset($data['amount']) || !isset($data['currency']) || !isset($data['payment_token'])) {
                throw new PaymentException('Amount, currency, and payment_token are required for PayPay payment.');
            }

            // Validate currency (PayPay primarily processes JPY)
            if (strtoupper($data['currency']) !== 'JPY') {
                throw new PaymentException('PayPay gateway primarily supports JPY currency.');
            }

            // Prepare PayPay payment data
            $paymentData = [
                'amount' => (int)round($data['amount']), // PayPay expects integer amount in JPY
                'currency' => 'JPY',
                'payment_token' => $data['payment_token'],
                'merchant_id' => $this->config['merchant_id'],
                'merchant_name' => $data['merchant_name'] ?? 'Unknown Merchant',
                'order_id' => $data['order_id'] ?? uniqid(),
            ];

            // Make API request to PayPay
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['api_key'],
                'Content-Type' => 'application/json',
            ])
            ->post($this->getPayPayEndpoint() . '/v2/payments', $paymentData);

            if ($response->successful()) {
                $result = $response->json();

                if (isset($result['status']) && $result['status'] === 'SUCCESS') {
                    return new PaymentResponse(
                        true,
                        $result['payment_id'],
                        [
                            'gatewayTransactionId' => $result['payment_id'],
                            'data' => [
                                'status' => $result['status'],
                                'payment_id' => $result['payment_id'],
                                'amount' => $result['amount'],
                                'currency' => $result['currency'],
                                'order_id' => $result['order_id'],
                                'authorized_at' => $result['authorized_at'] ?? null,
                            ],
                        ]
                    );
                }

                return new PaymentResponse(
                    false,
                    $result['payment_id'] ?? null,
                    [
                        'errorMessage' => $result['message'] ?? 'PayPay payment failed',
                        'gatewayTransactionId' => $result['payment_id'] ?? null,
                        'data' => [
                            'status' => $result['status'] ?? 'FAILED',
                            'message' => $result['message'] ?? null,
                        ],
                    ]
                );
            }

            // Handle API error
            $errorResult = $response->json();
            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $errorResult['message'] ?? 'Failed to connect to PayPay payment gateway.',
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
                'payment_id' => $transactionId,
                'refund_amount' => $amount !== null ? (int)round($amount) : null, // PayPay expects integer amount in JPY
                'refund_reason' => $data['refund_reason'] ?? 'Customer request',
            ];

            // Remove null values
            $refundData = array_filter($refundData);

            // Make API request to PayPay refund endpoint
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['api_key'],
                'Content-Type' => 'application/json',
            ])
            ->post($this->getPayPayEndpoint() . '/v2/refunds', $refundData);

            if ($response->successful()) {
                $result = $response->json();

                if (isset($result['status']) && $result['status'] === 'SUCCESS') {
                    return new PaymentResponse(
                        true,
                        $result['refund_id'],
                        [
                            'gatewayTransactionId' => $result['refund_id'],
                            'data' => [
                                'status' => $result['status'],
                                'refund_id' => $result['refund_id'],
                                'payment_id' => $result['payment_id'],
                                'amount' => $result['refund_amount'],
                                'currency' => $result['currency'],
                                'reason' => $result['reason'],
                            ],
                        ]
                    );
                }

                return new PaymentResponse(
                    false,
                    $result['refund_id'] ?? null,
                    [
                        'errorMessage' => $result['message'] ?? 'PayPay refund failed',
                        'gatewayTransactionId' => $result['refund_id'] ?? null,
                        'data' => [
                            'status' => $result['status'] ?? 'FAILED',
                            'message' => $result['message'] ?? null,
                        ],
                    ]
                );
            }

            // Handle API error
            $errorResult = $response->json();
            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $errorResult['message'] ?? 'Failed to connect to PayPay payment gateway for refund.',
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
     * @param string $transactionId Transaction ID to cancel
     * @return PaymentResponse
     */
    public function cancel(string $transactionId): PaymentResponse
    {
        try {
            // For PayPay, we'll check if we can cancel the payment
            // Otherwise, we'll suggest a refund for completed transactions
            
            return new PaymentResponse(
                false,
                $transactionId,
                [
                    'errorMessage' => 'Direct cancellation is not supported for PayPay. Please use refund for completed payments.',
                    'gatewayTransactionId' => $transactionId,
                    'data' => [
                        'suggestion' => 'Use refund method instead of cancel for PayPay gateway.',
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
     * Note: PayPay gateway primarily handles one-time payments.
     * Recurring payments would need custom implementation.
     *
     * @param array $data Subscription data
     * @return Subscription
     */
    public function subscribe(array $data): \ShamimStack\WwwPay\Models\Subscription
    {
        // PayPay doesn't natively support subscriptions through their standard API.
        # For recurring payments, merchants would need to implement custom solutions.
        return new \ShamimStack\WwwPay\Models\Subscription([
            'gateway' => 'paypay',
            'gateway_subscription_id' => 'sub_paypay_' . uniqid(),
            'status' => 'active',
            'plan_id' => $data['plan_id'] ?? null,
            'customer_id' => $data['customer_id'] ?? null,
            'start_date' => now(),
            'end_date' => null,
            'data' => [
                'note' => 'PayPay gateway does not natively support subscriptions. Recurring payments require custom implementation.',
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
            # Get the payload
            $payload = $request->all(); # PayPay sends data as POST parameters
            
            if (empty($payload)) {
                $payload = json_decode($request->getContent(), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return false;
                }
            }
            
            # Verify the webhook if needed
            # Implementation depends on PayPay's webhook security mechanism
            
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
        return 'paypay';
    }

    /**
     * Get the PayPay endpoint
     *
     * @return string
     */
    protected function getPayPayEndpoint(): string
    {
        if ($this->config['mode'] === 'live') {
            return 'https://api.paypay.ne.jp';
        } else {
            return 'https://staging-api.paypay.ne.jp';
        }
    }
}