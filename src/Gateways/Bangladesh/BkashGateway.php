<?php

namespace ShamimStack\AllInOnePayment\Gateways\Bangladesh;

use ShamimStack\AllInOnePayment\Contracts\PaymentGateway;
use ShamimStack\AllInOnePayment\Contracts\PaymentResponse;
use ShamimStack\AllInOnePayment\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BkashGateway implements PaymentGateway
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
                throw new PaymentException('Amount, currency, and customer_phone are required for bKash payment.');
            }

            // Validate currency (bKash primarily processes BDT)
            if (strtoupper($data['currency']) !== 'BDT') {
                throw new PaymentException('bKash gateway primarily supports BDT currency.');
            }

            // Step 1: Get authentication token
            $tokenResponse = $this->getToken();
            if (!$tokenResponse->isSuccessful()) {
                return $tokenResponse;
            }

            $token = $tokenResponse->getData()['id_token'];

            // Step 2: Create payment
            $paymentData = [
                'mode' => '0011', // 0011 for payment
                'payerReference' => $data['customer_phone'],
                'payerReferenceType' => 'msisdn',
                'callbackURL' => $data['callback_url'] ?? url('/payment/callback/bkash'),
                'amount' => number_format($data['amount'], 2, '.', ''),
                'currency' => 'BDT',
                'intent' => 'sale',
                'merchantInvoiceNumber' => $data['merchant_invoice_number'] ?? uniqid(),
            ];

            $headers = [
                'Authorization' => 'Bearer ' . $token,
                'X-APP-Key' => $this->config['app_key'],
                'Content-Type' => 'application/json',
            ];

            $createResponse = Http::withHeaders($headers)
                ->post($this->getPaymentCreateEndpoint(), $paymentData);

            if ($createResponse->successful()) {
                $result = $createResponse->json();

                if (isset($result['statusCode']) && $result['statusCode'] === '0000') {
                    return new PaymentResponse(
                        false, // bKash requires user to complete payment on their app/USSD
                        $result['paymentID'],
                        [
                            'gatewayTransactionId' => $result['paymentID'],
                            'data' => [
                                'statusCode' => $result['statusCode'],
                                'statusMessage' => $result['statusMessage'],
                                'bkashURL' => $result['bkashURL'] ?? null, // URL for user to complete payment
                                'amount' => $result['amount'],
                                'currency' => $result['currency'],
                                'expiryDate' => $result['expiryDate'],
                            ],
                        ]
                    );
                }

                return new PaymentResponse(
                    false,
                    null,
                    [
                        'errorMessage' => $result['statusMessage'] ?? 'Payment initiation failed',
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => 'Failed to connect to bKash payment gateway.',
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

            // Get authentication token
            $tokenResponse = $this->getToken();
            if (!$tokenResponse->isSuccessful()) {
                return $tokenResponse;
            }

            $token = $tokenResponse->getData()['id_token'];

            // Prepare refund request
            $refundData = [
                'paymentID' => $transactionId,
                'amount' => $amount !== null ? number_format($amount, 2, '.', '') : null,
                'currency' => 'BDT',
                'merchantInvoiceNumber' => 'REF_' . uniqid(),
            ];

            // Remove null values
            $refundData = array_filter($refundData);

            $headers = [
                'Authorization' => 'Bearer ' . $token,
                'X-APP-Key' => $this->config['app_key'],
                'Content-Type' => 'application/json',
            ];

            $refundResponse = Http::withHeaders($headers)
                ->post($this->getRefundEndpoint(), $refundData);

            if ($refundResponse->successful()) {
                $result = $refundResponse->json();

                if (isset($result['statusCode']) && $result['statusCode'] === '0000') {
                    return new PaymentResponse(
                        true,
                        $result['paymentID'],
                        [
                            'gatewayTransactionId' => $result['paymentID'],
                            'data' => [
                                'statusCode' => $result['statusCode'],
                                'statusMessage' => $result['statusMessage'],
                                'amount' => $result['amount'],
                                'currency' => $result['currency'],
                            ],
                        ]
                    );
                }

                return new PaymentResponse(
                    false,
                    $result['paymentID'] ?? null,
                    [
                        'errorMessage' => $result['statusMessage'] ?? 'Refund failed',
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => 'Failed to connect to bKash payment gateway for refund.',
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
     * Note: bKash doesn't support direct cancellation of initiated payments.
     * Payments expire automatically if not completed within the time limit.
     *
     * @param string $transactionId Transaction ID to cancel
     * @return PaymentResponse
     */
    public function cancel(string $transactionId): PaymentResponse
    {
        try {
            // bKash payments cannot be canceled once initiated - they expire automatically
            // after the expiry time if not completed by the user
            return new PaymentResponse(
                false,
                $transactionId,
                [
                    'errorMessage' => 'bKash payments cannot be canceled. Payments expire automatically if not completed.',
                    'gatewayTransactionId' => $transactionId,
                    'data' => [
                        'suggestion' => 'Wait for payment to expire or inform customer to not complete the payment.',
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
     * Note: bKash primarily handles one-time payments.
     * Recurring payments would need custom implementation.
     *
     * @param array $data Subscription data
     * @return Subscription
     */
    public function subscribe(array $data): \ShamimStack\AllInOnePayment\Models\Subscription
    {
        // bKash doesn't natively support subscriptions through their standard API.
        // For recurring payments, merchants would need to implement custom solutions.
        return new \ShamimStack\AllInOnePayment\Models\Subscription([
            'gateway' => 'bkash',
            'gateway_subscription_id' => 'sub_bkash_' . uniqid(),
            'status' => 'active',
            'plan_id' => $data['plan_id'] ?? null,
            'customer_id' => $data['customer_phone'] ?? null,
            'start_date' => now(),
            'end_date' => null,
            'data' => [
                'note' => 'bKash gateway does not natively support subscriptions. Recurring payments require custom implementation.',
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
            
            // bKash sends webhook data as form parameters or JSON
            $input = $request->all();
            
            if (empty($input)) {
                $input = json_decode($payload, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return false;
                }
            }
            
            // Verify the webhook signature if needed
            // bKash may use signature verification - implement according to their docs
            
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
        return 'bkash';
    }

    /**
     * Get bKash authentication token
     *
     * @return PaymentResponse
     */
    protected function getToken(): PaymentResponse
    {
        try {
            $tokenData = [
                'app_key' => $this->config['app_key'],
                'app_secret' => $this->config['app_secret'],
                'username' => $this->config['username'],
                'password' => $this->config['password'],
            ];

            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ];

            $response = Http::withHeaders($headers)
                ->post($this->getTokenEndpoint(), $tokenData);

            if ($response->successful()) {
                $result = $response->json();

                if (isset($result['statusCode']) && $result['statusCode'] === '0000') {
                    return new PaymentResponse(
                        true,
                        null,
                        [
                            'data' => [
                                'id_token' => $result['id_token'],
                            ],
                        ]
                    );
                }

                return new PaymentResponse(
                    false,
                    null,
                    [
                        'errorMessage' => $result['statusMessage'] ?? 'Failed to get bKash token',
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => 'Failed to connect to bKash token endpoint.',
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
     * Get the bKash token endpoint
     *
     * @return string
     */
    protected function getTokenEndpoint(): string
    {
        if ($this->config['mode'] === 'live') {
            return 'https://tokenized.bka.sh/v1.2.0-tokenized/checkout/token/grant';
        } else {
            return 'https://tokenized.sandbox.bka.sh/v1.2.0-tokenized/checkout/token/grant';
        }
    }

    /**
     * Get the bKash payment creation endpoint
     *
     * @return string
     */
    protected function getPaymentCreateEndpoint(): string
    {
        if ($this->config['mode'] === 'live') {
            return 'https://tokenized.bka.sh/v1.2.0-tokenized/checkout/create';
        } else {
            return 'https://tokenized.sandbox.bka.sh/v1.2.0-tokenized/checkout/create';
        }
    }

    /**
     * Get the bKash refund endpoint
     *
     * @return string
     */
    protected function getRefundEndpoint(): string
    {
        if ($this->config['mode'] === 'live') {
            return 'https://tokenized.bka.sh/v1.2.0-tokenized/checkout/execute';
        } else {
            return 'https://tokenized.sandbox.bka.sh/v1.2.0-tokenized/checkout/execute';
        }
    }
}