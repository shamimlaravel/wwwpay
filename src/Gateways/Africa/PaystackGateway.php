<?php

namespace ShamimStack\WwwPay\Gateways\Africa;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaystackGateway implements PaymentGateway
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
            if (!isset($data['amount']) || !isset($data['currency']) || !isset($data['email'])) {
                throw new PaymentException('Amount, currency, and email are required for Paystack payment.');
            }

            // Validate currency (Paystack primarily processes NGN, but supports others)
            // We'll allow multiple currencies but NGN is primary

            // Prepare Paystack payment data
            $paymentData = [
                'amount' => (int)round($data['amount'] * 100), // Paystack expects amount in kobo (NGN) or cents for other currencies
                'currency' => strtoupper($data['currency']),
                'email' => $data['email'],
                'reference' => $data['reference'] ?? uniqid(),
                'callback_url' => $data['callback_url'] ?? url('/payment/callback/paystack'),
                'metadata' => [
                    'customer_name' => $data['customer_name'] ?? '',
                    'customer_phone' => $data['customer_phone'] ?? '',
                ],
            ];

            // Make API request to Paystack
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['secret_key'],
                'Content-Type' => 'application/json',
            ])
            ->post($this->getPaystackEndpoint() . '/transaction/initialize', $paymentData);

            if ($response->successful()) {
                $result = $response->json();

                if (isset($result['status']) && $result['status'] === true && isset($result['data'])) {
                    $data = $result['data'];
                    
                    return new PaymentResponse(
                        false, // Paystack requires redirect to complete payment
                        $data['reference'],
                        [
                            'gatewayTransactionId' => $data['reference'],
                            'data' => [
                                'status' => 'pending',
                                'reference' => $data['reference'],
                                'amount' => $data['amount'] / 100,
                                'currency' => $data['currency'],
                                'authorization_url' => $data['authorization_url'],
                                'access_code' => $data['access_code'],
                            ],
                        ]
                    );
                }

                return new PaymentResponse(
                    false,
                    null,
                    [
                        'errorMessage' => $result['message'] ?? 'Paystack payment initialization failed',
                    ]
                );
            }

            // Handle API error
            $errorResult = $response->json();
            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $errorResult['message'] ?? 'Failed to connect to Paystack payment gateway.',
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
                'transaction' => $transactionId,
                'amount' => $amount !== null ? (int)round($amount * 100) : null, // Paystack expects amount in kobo/cents
            ];

            // Remove null values
            $refundData = array_filter($refundData);

            // Make API request to Paystack refund endpoint
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['secret_key'],
                'Content-Type' => 'application/json',
            ])
            ->post($this->getPaystackEndpoint() . '/refund', $refundData);

            if ($response->successful()) {
                $result = $response->json();

                if (isset($result['status']) && $result['status'] === true) {
                    $data = $result['data'];
                    
                    return new PaymentResponse(
                        true,
                        $data['id'],
                        [
                            'gatewayTransactionId' => $data['id'],
                            'data' => [
                                'id' => $data['id'],
                                'transaction' => $data['transaction'],
                                'amount' => $data['amount'] / 100,
                                'currency' => $data['currency'],
                                'status' => $data['status'],
                                'gateway_response' => $data,
                            ],
                        ]
                    );
                }

                return new PaymentResponse(
                    false,
                    null,
                    [
                        'errorMessage' => $result['message'] ?? 'Paystack refund failed',
                    ]
                );
            }

            // Handle API error
            $errorResult = $response->json();
            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $errorResult['message'] ?? 'Failed to connect to Paystack payment gateway for refund.',
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
     * Note: Paystack doesn't support direct cancellation of processed payments.
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
                    'errorMessage' => 'Direct cancellation is not supported for Paystack. Please use refund for settled transactions.',
                    'gatewayTransactionId' => $transactionId,
                    'data' => [
                        'suggestion' => 'Use refund method instead of cancel for Paystack gateway.',
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
     * Note: Paystack gateway supports subscriptions through their Subscription API.
     *
     * @param array $data Subscription data
     * @return Subscription
     */
    public function subscribe(array $data): \ShamimStack\WwwPay\Models\Subscription
    {
        # Paystack supports subscriptions through their Subscription API.
        # For demonstration, we'll return a basic subscription model.
        
        return new \ShamimStack\WwwPay\Models\Subscription([
            'gateway' => 'paystack',
            'gateway_subscription_id' => 'sub_paystack_' . uniqid(),
            'status' => 'active',
            'plan_id' => $data['plan_id'] ?? null,
            'customer_id' => $data['customer_email'] ?? null,
            'start_date' => now(),
            'end_date' => null, # For ongoing subscriptions
            'data' => [
                'note' => 'Paystack gateway supports subscriptions via Subscription API.',
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
            $payload = $request->getContent();
            
            # Get the signature from headers
            $signature = $request->header('x-paystack-signature');
            
            # Verify the webhook signature if secret key is configured
            if (!empty($this->config['secret_key']) && !empty($signature)) {
                $hash = hash_hmac('sha512', $payload, $this->config['secret_key']);
                
                if (!hash_equals($hash, $signature)) {
                    # Invalid signature
                    return false;
                }
            }
            
            # Parse the payload
            $data = json_decode($payload, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return false;
            }
            
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
        return 'paystack';
    }

    /**
     * Get the Paystack endpoint
     *
     * @return string
     */
    protected function getPaystackEndpoint(): string
    {
        if ($this->config['mode'] === 'live') {
            return 'https://api.paystack.co';
        } else {
            return 'https://api.paystack.co';
        }
    }
}