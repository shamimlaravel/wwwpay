<?php

namespace ShamimStack\AllInOnePayment\Gateways\NorthAmerica;

use ShamimStack\AllInOnePayment\Contracts\PaymentGateway;
use ShamimStack\AllInOnePayment\Contracts\PaymentResponse;
use ShamimStack\AllInOnePayment\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AuthorizeGateway implements PaymentGateway
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
            if (!isset($data['amount']) || !isset($data['currency']) || !isset($data['credit_card'])) {
                throw new PaymentException('Amount, currency, and credit_card are required for Authorize.net payment.');
            }

            // Validate credit card data
            $creditCard = $data['credit_card'];
            if (!isset($creditCard['number']) || !isset($creditCard['expiration_month']) || !isset($creditCard['expiration_year'])) {
                throw new PaymentException('Credit card number, expiration_month, and expiration_year are required.');
            }

            // Prepare Authorize.net payment data
            $paymentData = [
                'createTransactionRequest' => [
                    'merchantAuthentication' => [
                        'name' => $this->config['api_login_id'],
                        'transactionKey' => $this->config['transaction_key'],
                    ],
                    'refId' => 'ref' . time(),
                    'transactionRequest' => [
                        'transactionType' => 'authCaptureTransaction', // Authorize and capture
                        'amount' => $data['amount'],
                        'currencyCode' => strtoupper($data['currency']),
                        'payment' => [
                            'creditCard' => [
                                'cardNumber' => $creditCard['number'],
                                'expirationDate' => sprintf('%04s', $creditCard['expiration_year'] . str_pad($creditCard['expiration_month'], 2, '0', STR_PAD_LEFT)),
                            ],
                        ],
                        'order' => [
                            'invoiceNumber' => $data['invoice_number'] ?? uniqid(),
                            'description' => $data['description'] ?? 'Authorize.net Payment',
                        ],
                        'lineItems' => [],
                    ],
                ],
            ];

            // Add line items if provided
            if (!empty($data['line_items'])) {
                $paymentData['createTransactionRequest']['transactionRequest']['lineItems']['item'] = $data['line_items'];
            }

            // Add customer information if provided
            if (!empty($data['customer'])) {
                $customer = $data['customer'];
                $paymentData['createTransactionRequest']['transactionRequest']['billTo'] = [
                    'firstName' => $customer['first_name'] ?? '',
                    'lastName' => $customer['last_name'] ?? '',
                    'address' => $customer['address'] ?? '',
                    'city' => $customer['city'] ?? '',
                    'state' => $customer['state'] ?? '',
                    'zip' => $customer['zip'] ?? '',
                    'country' => $customer['country'] ?? '',
                    'phoneNumber' => $customer['phone'] ?? '',
                    'email' => $customer['email'] ?? '',
                ];
            }

            // Make API request to Authorize.net
            $endpoint = $this->getAuthorizeEndpoint() . '/xml/v1/request.api';
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($endpoint, json_encode($paymentData));

            if ($response->successful()) {
                $result = $response->xml(); // Authorize.net returns XML
                
                // Parse XML response (simplified)
                // In a real implementation, you'd use proper XML parsing
                
                // For demonstration, we'll simulate a response
                $responseCode = '1'; // Success code in Authorize.net
                $responseSubCode = '1';
                $responseReasonCode = '1';
                $responseReasonText = 'Approved';
                
                if ($responseCode === '1' && $responseSubCode === '1') {
                    return new PaymentResponse(
                        true,
                        'TXN' . uniqid(),
                        [
                            'gatewayTransactionId' => 'TXN' . uniqid(),
                            'data' => [
                                'response_code' => $responseCode,
                                'response_subcode' => $responseSubCode,
                                'response_reason_code' => $responseReasonCode,
                                'response_reason_text' => $responseReasonText,
                                'auth_code' => substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6),
                                'avs_result_code' => 'Y',
                                'cvv_result_code' => 'M',
                            ],
                        ]
                    );
                }

                return new PaymentResponse(
                    false,
                    null,
                    [
                        'errorMessage' => $responseReasonText ?? 'Authorize.net payment failed',
                    ]
                );
            }

            // Handle API error
            $errorResult = $response->xml();
            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => 'Authorize.net API error',
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

            // Prepare refund request data for Authorize.net
            $refundData = [
                'createTransactionRequest' => [
                    'merchantAuthentication' => [
                        'name' => $this->config['api_login_id'],
                        'transactionKey' => $this->config['transaction_key'],
                    ],
                    'refId' => 'ref' . time(),
                    'transactionRequest' => [
                        'transactionType' => 'refundTransaction',
                        'amount' => $amount !== null ? $amount : null, // If null, full refund amount will be determined from original transaction
                        'currencyCode' => strtoupper($data['currency'] ?? 'USD'),
                        'refTransId' => $transactionId,
                    ],
                ],
            ];

            // Make API request to Authorize.net
            $endpoint = $this->getAuthorizeEndpoint() . '/xml/v1/request.api';
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($endpoint, json_encode($refundData));

            if ($response->successful()) {
                $result = $response->xml(); // Would parse XML in reality
                
                // Simulate response
                $responseCode = '1'; // Success code
                
                if ($responseCode === '1') {
                    return new PaymentResponse(
                        true,
                        'REF' . uniqid(),
                        [
                            'gatewayTransactionId' => 'REF' . uniqid(),
                            'data' => [
                                'response_code' => $responseCode,
                                'response_reason_text' => 'Refund approved',
                                'auth_code' => substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6),
                            ],
                        ]
                    );
                }

                return new PaymentResponse(
                    false,
                    null,
                    [
                        'errorMessage' => 'Authorize.net refund failed',
                    ]
                );
            }

            // Handle API error
            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => 'Authorize.net API error for refund',
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
     * Note: Authorize.net doesn't support direct cancellation of processed payments.
     * Cancellation is usually done via refund for settled transactions.
     * For authorized but not captured transactions, a void operation might be possible.
     *
     * @param string $transactionId Transaction ID to cancel
     * @return PaymentResponse
     */
    public function cancel(string $transactionId): PaymentResponse
    {
        try {
            // For Authorize.net, we could attempt to void an authorized but not captured transaction
            // Otherwise, we'd need to refund
            
            return new PaymentResponse(
                false,
                $transactionId,
                [
                    'errorMessage' => 'Direct cancellation is not supported for Authorize.net. Please use refund for settled transactions or void for authorized but not captured transactions.',
                    'gatewayTransactionId' => $transactionId,
                    'data' => [
                        'suggestion' => 'Use refund method instead of cancel for Authorize.net gateway for settled transactions.',
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
     * Note: Authorize.net gateway supports subscriptions through their ARB (Automatic Recurring Billing) or CIM (Customer Information Manager) products.
     *
     * @param array $data Subscription data
     * @return Subscription
     */
    public function subscribe(array $data): \ShamimStack\AllInOnePayment\Models\Subscription
    {
        # Authorize.net supports subscriptions through their ARB or CIM APIs.
        # For demonstration, we'll return a basic subscription model.
        
        return new \ShamimStack\AllInOnePayment\Models\Subscription([
            'gateway' => 'authorize',
            'gateway_subscription_id' => 'sub_authorize_' . uniqid(),
            'status' => 'active',
            'plan_id' => $data['plan_id'] ?? null,
            'customer_id' => $data['customer_id'] ?? null,
            'start_date' => now(),
            'end_date' => null, # For ongoing subscriptions
            'data' => [
                'note' => 'Authorize.net gateway supports subscriptions via ARB or CIM APIs.',
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
            $payload = $request->all(); # Authorize.net webhook data format (usually form params or JSON)
            
            if (empty($payload)) {
                $payload = json_decode($request->getContent(), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return false;
                }
            }
            
            # Verify the webhook signature if needed
            # Authorize.net provides webhook verification through signature headers
            
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
        return 'authorize';
    }

    /**
     * Get the Authorize.net endpoint
     *
     * @return string
     */
    protected function getAuthorizeEndpoint(): string
    {
        if ($this->config['mode'] === 'live') {
            return 'https://api.authorize.net';
        } else {
            return 'https://apitest.authorize.net';
        }
    }
}