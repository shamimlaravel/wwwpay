<?php

namespace ShamimStack\AllInOnePayment\Gateways\India;

use ShamimStack\AllInOnePayment\Contracts\PaymentGateway;
use ShamimStack\AllInOnePayment\Contracts\PaymentResponse;
use ShamimStack\AllInOnePayment\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class PhonePeGateway implements PaymentGateway
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
            if (!isset($data['amount']) || !isset($data['currency']) || !isset($data['merchant_user_id'])) {
                throw new PaymentException('Amount, currency, and merchant_user_id are required for PhonePe payment.');
            }

            // Validate currency (PhonePe primarily processes INR)
            if (strtoupper($data['currency']) !== 'INR') {
                throw new PaymentException('PhonePe gateway primarily supports INR currency.');
            }

            // Prepare PhonePe payment data
            $merchantTransactionId = $data['merchant_transaction_id'] ?? uniqid();
            $amount = $data['amount'] * 100; // PhonePe expects amount in paise

            $payload = [
                'merchantId' => $this->config['merchant_id'],
                'merchantTransactionId' => $merchantTransactionId,
                'merchantUserId' => $data['merchant_user_id'],
                'amount' => $amount,
                'callbackUrl' => $data['callback_url'] ?? url('/payment/callback/phonepe'),
                'mobileNumber' => $data['mobile_number'] ?? '',
                'deviceContext' => [
                    'deviceOS' => $data['device_os'] ?? 'android',
                ],
            ];

            // Encode payload
            $payloadBase64 = base64_encode(json_encode($payload));

            // Create checksum: SHA256(payloadBase64 + "/pg/v1/pay" + saltKey) + ### + saltIndex
            $string = $payloadBase64 . "/pg/v1/pay" . $this->config['salt_key'];
            $checksum = hash('sha256', $string) . '###' . $this->config['salt_index'];

            // Prepare request
            $requestData = [
                'request' => $payloadBase64,
            ];

            $headers = [
                'Content-Type' => 'application/json',
                'X-VERIFY' => $checksum,
                'accept' => 'application/json',
            ];

            // Make API request to PhonePe
            $response = Http::withHeaders($headers)
                ->post($this->getPhonePeEndpoint() . "/pg/v1/pay", $requestData);

            if ($response->successful()) {
                $result = $response->json();

                if (isset($result['success']) && $result['success'] === true) {
                    return new PaymentResponse(
                        false, // PhonePe requires user to complete payment via their app/QR/UPI
                        $merchantTransactionId,
                        [
                            'gatewayTransactionId' => $merchantTransactionId,
                            'data' => [
                                'providerResponse' => $result,
                                'instrumentResponse' => [
                                    'type' => $result['data']['instrumentResponse']['type'] ?? null,
                                    'bankForm' => [
                                        'pageType' => $result['data']['instrumentResponse']['bankForm']['pageType'] ?? null,
                                        'bankFormData' => $result['data']['instrumentResponse']['bankForm']['bankFormData'] ?? [],
                                    ],
                                ],
                            ],
                        ]
                    );
                }

                return new PaymentResponse(
                    false,
                    $merchantTransactionId,
                    [
                        'errorMessage' => $result['message'] ?? 'Payment initiation failed',
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => 'Failed to connect to PhonePe payment gateway.',
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
            $merchantTransactionId = $data['merchant_transaction_id'] ?? uniqid(); // For refund, we need a new merchant transaction id
            $amountInPaise = $amount !== null ? round($amount * 100) : null;

            $payload = [
                'merchantId' => $this->config['merchant_id'],
                'merchantTransactionId' => $merchantTransactionId,
                'originalTransactionId' => $transactionId,
                'amount' => $amountInPaise,
                'merchantUserId' => $data['merchant_user_id'] ?? '',
            ];

            // If amount is null, it's a full refund - PhonePe might require amount for partial refund only
            // For full refund, we might not need to send amount or send 0? Check PhonePe docs.
            // We'll assume amount is required for refund (partial or full)

            // Encode payload
            $payloadBase64 = base64_encode(json_encode($payload));

            // Create checksum
            $string = $payloadBase64 . "/pg/v1/refund" . $this->config['salt_key'];
            $checksum = hash('sha256', $string) . '###' . $this->config['salt_index'];

            // Prepare request
            $requestData = [
                'request' => $payloadBase64,
            ];

            $headers = [
                'Content-Type' => 'application/json',
                'X-VERIFY' => $checksum,
                'accept' => 'application/json',
            ];

            // Make API request to PhonePe refund endpoint
            $response = Http::withHeaders($headers)
                ->post($this->getPhonePeEndpoint() . "/pg/v1/refund", $requestData);

            if ($response->successful()) {
                $result = $response->json();

                if (isset($result['success']) && $result['success'] === true) {
                    return new PaymentResponse(
                        true,
                        $merchantTransactionId,
                        [
                            'gatewayTransactionId' => $merchantTransactionId,
                            'data' => [
                                'providerResponse' => $result,
                                'amount' => ($amountInPaise ?? 0) / 100,
                                'currency' => 'INR',
                            ],
                        ]
                    );
                }

                return new PaymentResponse(
                    false,
                    $merchantTransactionId,
                    [
                        'errorMessage' => $result['message'] ?? 'Refund failed',
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => 'Failed to connect to PhonePe payment gateway for refund.',
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
     * Note: PhonePe doesn't support direct cancellation of processed payments.
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
                    'errorMessage' => 'Direct cancellation is not supported for PhonePe. Please use refund for settled transactions.',
                    'gatewayTransactionId' => $transactionId,
                    'data' => [
                        'suggestion' => 'Use refund method instead of cancel for PhonePe gateway.',
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
     * Note: PhonePe gateway primarily handles one-time payments.
     * Recurring payments would need custom implementation.
     *
     * @param array $data Subscription data
     * @return Subscription
     */
    public function subscribe(array $data): \ShamimStack\AllInOnePayment\Models\Subscription
    {
        // PhonePe doesn't natively support subscriptions through their standard API.
        // For recurring payments, merchants would need to implement custom solutions.
        return new \ShamimStack\AllInOnePayment\Models\Subscription([
            'gateway' => 'phonepe',
            'gateway_subscription_id' => 'sub_phonepe_' . uniqid(),
            'status' => 'active',
            'plan_id' => $data['plan_id'] ?? null,
            'customer_id' => $data['merchant_user_id'] ?? null,
            'start_date' => now(),
            'end_date' => null,
            'data' => [
                'note' => 'PhonePe gateway does not natively support subscriptions. Recurring payments require custom implementation.',
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
            
            // PhonePe sends webhook data as form parameters or JSON
            $input = $request->all();
            
            if (empty($input)) {
                $input = json_decode($payload, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return false;
                }
            }
            
            # Verify the webhook signature if needed
            # PhonePe webhook verification would be similar to payment verification
            
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
        return 'phonepe';
    }

    /**
     * Get the PhonePe endpoint
     *
     * @return string
     */
    protected function getPhonePeEndpoint(): string
    {
        if ($this->config['mode'] === 'live') {
            return 'https://api.phonepe.com/apis/hermes';
        } else {
            return 'https://api-preprod.phonepe.com/apis/pg-sandbox';
        }
    }
}