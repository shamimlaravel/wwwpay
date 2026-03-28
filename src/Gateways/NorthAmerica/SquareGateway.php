<?php

namespace ShamimStack\AllInOnePayment\Gateways\NorthAmerica;

use ShamimStack\AllInOnePayment\Contracts\PaymentGateway;
use ShamimStack\AllInOnePayment\Contracts\PaymentResponse;
use ShamimStack\AllInOnePayment\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;

class SquareGateway implements PaymentGateway
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
            if (!isset($data['amount']) || !isset($data['currency']) || !isset($data['source_id'])) {
                throw new PaymentException('Amount, currency, and source_id are required for Square payment.');
            }

            // Prepare Square payment data
            $paymentData = [
                'source_id' => $data['source_id'],
                'idempotency_key' => uniqid(),
                'amount_money' => [
                    'amount' => round($data['amount'] * 100), // Square expects amount in cents
                    'currency' => $data['currency'],
                ],
                'reference_id' => $data['reference_id'] ?? uniqid(),
                'note' => $data['note'] ?? 'Square Payment',
            ];

            // Add customer ID if provided
            if (!empty($data['customer_id'])) {
                $paymentData['customer_id'] = $data['customer_id'];
            }

            // Make API request to Square
            $response = Http::withHeaders([
                'Square-Version' => '2023-09-20',
                'Authorization' => 'Bearer ' . $this->config['access_token'],
                'Content-Type' => 'application/json',
            ])
            ->post($this->getSquareEndpoint() . '/v2/payments', $paymentData);

            if ($response->successful()) {
                $result = $response->json();

                if (isset($result['payment']) && isset($result['payment']['status']) && $result['payment']['status'] === 'COMPLETED') {
                    return new PaymentResponse(
                        true,
                        $result['payment']['id'],
                        [
                            'gatewayTransactionId' => $result['payment']['id'],
                            'data' => [
                                'status' => $result['payment']['status'],
                                'amount' => $result['payment']['amount_money']['amount'] / 100,
                                'currency' => $result['payment']['amount_money']['currency'],
                                'source_id' => $result['payment']['source_id'],
                                'reference_id' => $result['payment']['reference_id'],
                                'card_details' => $result['payment']['card_details'] ?? null,
                            ],
                        ]
                    );
                }

                // Payment processed but not completed (e.g., PENDING, FAILED)
                return new PaymentResponse(
                    false,
                    $result['payment']['id'] ?? null,
                    [
                        'errorMessage' => 'Square payment not completed. Status: ' . ($result['payment']['status'] ?? 'unknown'),
                        'gatewayTransactionId' => $result['payment']['id'] ?? null,
                        'data' => [
                            'status' => $result['payment']['status'] ?? 'unknown',
                            'amount' => ($result['payment']['amount_money']['amount'] ?? 0) / 100,
                            'currency' => $result['payment']['amount_money']['currency'] ?? '',
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
                    'errorMessage' => $errorResult['errors'][0]['detail'] ?? 'Square payment failed',
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

            // Prepare refund data
            $refundData = [
                'payment_id' => $transactionId,
                'idempotency_key' => uniqid(),
                'reason' => $data['reason'] ?? 'Customer request',
            ];

            // Add amount if specified (partial refund)
            if ($amount !== null) {
                $refundData['amount_money'] = [
                    'amount' => round($amount * 100), // Square expects amount in cents
                    'currency' => $data['currency'] ?? 'USD',
                ];
            }

            // Make API request to Square
            $response = Http::withHeaders([
                'Square-Version' => '2023-09-20',
                'Authorization' => 'Bearer ' . $this->config['access_token'],
                'Content-Type' => 'application/json',
            ])
            ->post($this->getSquareEndpoint() . '/v2/refunds', $refundData);

            if ($response->successful()) {
                $result = $response->json();

                if (isset($result['refund']) && isset($result['refund']['status']) && $result['refund']['status'] === 'COMPLETED') {
                    return new PaymentResponse(
                        true,
                        $result['refund']['id'],
                        [
                            'gatewayTransactionId' => $result['refund']['id'],
                            'data' => [
                                'status' => $result['refund']['status'],
                                'amount' => $result['refund']['amount_money']['amount'] / 100,
                                'currency' => $result['refund']['amount_money']['currency'],
                                'payment_id' => $result['refund']['payment_id'],
                                'reason' => $result['refund']['reason'],
                            ],
                        ]
                    );
                }

                // Refund processed but not completed
                return new PaymentResponse(
                    false,
                    $result['refund']['id'] ?? null,
                    [
                        'errorMessage' => 'Square refund not completed. Status: ' . ($result['refund']['status'] ?? 'unknown'),
                        'gatewayTransactionId' => $result['refund']['id'] ?? null,
                        'data' => [
                            'status' => $result['refund']['status'] ?? 'unknown',
                            'amount' => ($result['refund']['amount_money']['amount'] ?? 0) / 100,
                            'currency' => $result['refund']['amount_money']['currency'] ?? '',
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
                    'errorMessage' => $errorResult['errors'][0]['detail'] ?? 'Square refund failed',
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
     * Note: Square doesn't support direct cancellation of processed payments.
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
                    'errorMessage' => 'Direct cancellation is not supported for Square. Please use refund for settled transactions.',
                    'gatewayTransactionId' => $transactionId,
                    'data' => [
                        'suggestion' => 'Use refund method instead of cancel for Square gateway.',
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
     * Note: Square gateway primarily handles one-time payments.
     * Recurring payments would need custom implementation or use of Square's subscription products.
     *
     * @param array $data Subscription data
     * @return Subscription
     */
    public function subscribe(array $data): \ShamimStack\AllInOnePayment\Models\Subscription
    {
        // Square doesn't natively support subscriptions through a simple API like card gateways.
        # For recurring payments, Square offers subscription products or you would need to:
        # 1. Store customer payment credentials securely (PCI-DSS compliant)
        # 2. Initiate new payments on schedule
        # 3. Handle failed payments and retries
        
        return new \ShamimStack\AllInOnePayment\Models\Subscription([
            'gateway' => 'square',
            'gateway_subscription_id' => 'sub_square_' . uniqid(),
            'status' => 'active',
            'plan_id' => $data['plan_id'] ?? null,
            'customer_id' => $data['customer_id'] ?? null,
            'start_date' => now(),
            'end_date' => null,
            'data' => [
                'note' => 'Square gateway does not natively support subscriptions via standard API. Recurring payments require Square subscription products or custom implementation.',
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
            $payload = $request->all(); # Square webhook data format
            
            if (empty($payload)) {
                $payload = json_decode($request->getContent(), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return false;
                }
            }
            
            # Verify the webhook signature if needed
            # Square provides a signature header for webhook verification
            
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
        return 'square';
    }

    /**
     * Get the Square endpoint
     *
     * @return string
     */
    protected function getSquareEndpoint(): string
    {
        if ($this->config['mode'] === 'live') {
            return 'https://connect.squareup.com';
        } else {
            return 'https://connect.squareupsandbox.com';
        }
    }
}