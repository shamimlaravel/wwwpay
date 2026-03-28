<?php

namespace ShamimStack\WwwPay\Gateways\MiddleEast;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MadaGateway implements PaymentGateway
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
            if (!isset($data['amount']) || !isset($data['currency']) || !isset($data['card_number']) || !isset($data['expiry_month']) || !isset($data['expiry_year']) || !isset($data['cvv'])) {
                throw new PaymentException('Amount, currency, card_number, expiry_month, expiry_year, and cvv are required for Mada payment.');
            }

            // Validate currency (Mada typically processes SAR)
            if (strtoupper($data['currency']) !== 'SAR') {
                throw new PaymentException('Mada gateway only supports SAR currency.');
            }

            // Prepare request data for Mada API
            $requestData = [
                'merchant_id' => $this->config['merchant_id'],
                'terminal_id' => $this->config['terminal_id'],
                'amount' => number_format($data['amount'], 3, '.', ''), // Mada uses 3 decimal places
                'currency' => $data['currency'],
                'order_id' => $data['order_id'] ?? uniqid(),
                'language' => 'en',
                'card_number' => $data['card_number'],
                'expiry_month' => str_pad($data['expiry_month'], 2, '0', STR_PAD_LEFT),
                'expiry_year' => $data['expiry_year'],
                'cvv' => $data['cvv'],
                'merchant_reference' => $data['merchant_reference'] ?? '',
                'customer_email' => $data['customer_email'] ?? '',
                'customer_mobile' => $data['customer_mobile'] ?? '',
                'payment_option' => 'MASTERCARD', // Mada transactions are processed via Mastercard network
                'source' => 'ECI',
                'eci' => '05', // E-commerce indicator for Mada
                'return_url' => $data['return_url'] ?? url('/payment/callback'),
                'callback' => $data['callback'] ?? url('/payment/webhook/mada'),
            ];

            // Add additional security fields if needed
            if (!empty($this->config['secret_key'])) {
                $requestData['hashed'] = hash_hmac('sha256', json_encode($requestData), $this->config['secret_key']);
            }

            // Make API request to Mada (this is a simulated endpoint - replace with actual Mada API)
            $response = Http::post($this->getEndpoint(), $requestData);

            if ($response->successful()) {
                $result = $response->json();

                // Check if payment was successful
                if (isset($result['response_code']) && $result['response_code'] === '000') {
                    return new PaymentResponse(
                        true,
                        $result['transaction_id'] ?? $requestData['order_id'],
                        [
                            'gatewayTransactionId' => $result['transaction_id'] ?? $requestData['order_id'],
                            'data' => [
                                'response_code' => $result['response_code'],
                                'response_message' => $result['response_message'] ?? 'Transaction approved',
                                'authorization_code' => $result['authorization_code'] ?? null,
                                'amount' => $result['amount'] ?? $data['amount'],
                                'currency' => $result['currency'] ?? $data['currency'],
                            ],
                        ]
                    );
                }

                // Payment failed
                return new PaymentResponse(
                    false,
                    $result['transaction_id'] ?? $requestData['order_id'],
                    [
                        'errorMessage' => $result['response_message'] ?? 'Transaction failed',
                        'gatewayTransactionId' => $result['transaction_id'] ?? $requestData['order_id'],
                        'data' => [
                            'response_code' => $result['response_code'] ?? null,
                            'response_message' => $result['response_message'] ?? null,
                        ],
                    ]
                );
            }

            // HTTP request failed
            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => 'Failed to connect to Mada payment gateway.',
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
            $requestData = [
                'merchant_id' => $this->config['merchant_id'],
                'terminal_id' => $this->config['terminal_id'],
                'transaction_id' => $transactionId,
                'amount' => $amount !== null ? number_format($amount, 3, '.', '') : null, // If null, full refund
                'currency' => 'SAR',
                'refund_reason' => 'Customer request',
                'merchant_reference' => 'REFUND_' . uniqid(),
            ];

            // Remove null values
            $requestData = array_filter($requestData);

            // Add security hash
            if (!empty($this->config['secret_key'])) {
                $requestData['hashed'] = hash_hmac('sha256', json_encode($requestData), $this->config['secret_key']);
            }

            // Make API request to Mada refund endpoint
            $response = Http::post($this->getRefundEndpoint(), $requestData);

            if ($response->successful()) {
                $result = $response->json();

                // Check if refund was successful
                if (isset($result['response_code']) && $result['response_code'] === '000') {
                    return new PaymentResponse(
                        true,
                        $result['refund_transaction_id'] ?? $transactionId . '_REF',
                        [
                            'gatewayTransactionId' => $result['refund_transaction_id'] ?? $transactionId . '_REF',
                            'data' => [
                                'response_code' => $result['response_code'],
                                'response_message' => $result['response_message'] ?? 'Refund processed successfully',
                                'amount' => $result['amount'] ?? ($amount ?? 0),
                                'currency' => $result['currency'] ?? 'SAR',
                            ],
                        ]
                    );
                }

                // Refund failed
                return new PaymentResponse(
                    false,
                    $result['refund_transaction_id'] ?? $transactionId . '_REF',
                    [
                        'errorMessage' => $result['response_message'] ?? 'Refund failed',
                        'gatewayTransactionId' => $result['refund_transaction_id'] ?? $transactionId . '_REF',
                        'data' => [
                            'response_code' => $result['response_code'] ?? null,
                            'response_message' => $result['response_message'] ?? null,
                        ],
                    ]
                );
            }

            // HTTP request failed
            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => 'Failed to connect to Mada payment gateway for refund.',
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
     * Note: Mada doesn't typically support direct cancellation of processed payments.
     * Cancellation is usually done via refund for settled transactions.
     * For authorized but not captured transactions, a void operation might be possible.
     *
     * @param string $transactionId Transaction ID to cancel
     * @return PaymentResponse
     */
    public function cancel(string $transactionId): PaymentResponse
    {
        try {
            // For Mada, we'll attempt a void if the transaction is authorized but not captured
            // Otherwise, we'll suggest a refund
            
            // First, check transaction status (this would require a transaction inquiry API)
            // For simplicity, we'll return a response indicating that cancellation 
            // for Mada is typically done via refund for settled transactions
            
            return new PaymentResponse(
                false,
                $transactionId,
                [
                    'errorMessage' => 'Direct cancellation is not supported for Mada. Please use refund for settled transactions.',
                    'gatewayTransactionId' => $transactionId,
                    'data' => [
                        'suggestion' => 'Use refund method instead of cancel for Mada gateway.',
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
     * Note: Mada gateway primarily handles one-time payments.
     * Recurring payments would need to be implemented by storing card details securely
     * and initiating new transactions on schedule (which requires PCI compliance).
     * This method returns a basic subscription model.
     *
     * @param array $data Subscription data
     * @return Subscription
     */
    public function subscribe(array $data): \ShamimStack\WwwPay\Models\Subscription
    {
        // Mada doesn't natively support subscriptions through their standard API.
        // For recurring payments, merchants would need to:
        // 1. Securely store tokenized card data (PCI-DSS compliant)
        // 2. Initiate new payments on schedule
        // 3. Handle expired cards and updates
        
        // We'll return a basic subscription model as a placeholder
        return new \ShamimStack\WwwPay\Models\Subscription([
            'gateway' => 'mada',
            'gateway_subscription_id' => 'sub_mada_' . uniqid(),
            'status' => 'active',
            'plan_id' => $data['plan_id'] ?? null,
            'customer_id' => $data['customer_id'] ?? null,
            'start_date' => now(),
            'end_date' => null, // For ongoing subscriptions
            'data' => [
                'note' => 'Mada gateway does not natively support subscriptions. Recurring payments require custom implementation.',
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
            
            // Get the signature from headers (implementation depends on Mada's webhook security)
            $signature = $request->header('X-Mada-Signature') ?? $request->header('mada-signature');
            
            // Verify the webhook signature if secret key is configured
            if (!empty($this->config['secret_key']) && !empty($signature)) {
                $expectedSignature = hash_hmac('sha256', $payload, $this->config['secret_key']);
                
                if (!hash_equals($expectedSignature, $signature)) {
                    // Invalid signature
                    return false;
                }
            }
            
            // Parse the payload
            $data = json_decode($payload, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return false;
            }
            
            // Process the webhook data (this would depend on Mada's webhook format)
            // For example, handle payment completion, refunds, etc.
            
            // In a real implementation, you would:
            // 1. Log the webhook received
            // 2. Verify the transaction status with Mada if needed
            // 3. Update your database records
            // 4. Send notifications if required
            
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
        return 'mada';
    }

    /**
     * Get the Mada API endpoint for payments
     *
     * @return string
     */
    protected function getEndpoint(): string
    {
        // This is a placeholder - replace with actual Mada endpoint
        if ($this->config['mode'] === 'live') {
            return 'https://api.mada.com.sa/payments';
        } else {
            return 'https://apitest.mada.com.sa/payments';
        }
    }

    /**
     * Get the Mada API endpoint for refunds
     *
     * @return string
     */
    protected function getRefundEndpoint(): string
    {
        // This is a placeholder - replace with actual Mada refund endpoint
        if ($this->config['mode'] === 'live') {
            return 'https://api.mada.com.sa/refunds';
        } else {
            return 'https://apitest.mada.com.sa/refunds';
        }
    }
}