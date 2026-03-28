<?php

namespace ShamimStack\AllInOnePayment\Gateways\SouthAfrica;

use ShamimStack\AllInOnePayment\Contracts\PaymentGateway;
use ShamimStack\AllInOnePayment\Contracts\PaymentResponse;
use ShamimStack\AllInOnePayment\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PayFastGateway implements PaymentGateway
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
            if (!isset($data['amount']) || !isset($data['currency']) || !isset($data['item_name'])) {
                throw new PaymentException('Amount, currency, and item_name are required for PayFast payment.');
            }

            // Validate currency (PayFast primarily processes ZAR)
            if (strtoupper($data['currency']) !== 'ZAR') {
                throw new PaymentException('PayFast gateway primarily supports ZAR currency.');
            }

            // Prepare PayFast payment data
            $paymentData = [
                'merchant_id' => $this->config['merchant_id'],
                'merchant_key' => $this->config['merchant_key'],
                'return_url' => $data['return_url'] ?? url('/payment/payfast/return'),
                'cancel_url' => $data['cancel_url'] ?? url('/payment/payfast/cancel'),
                'notify_url' => $data['notify_url'] ?? url('/payment/payfast/notify'),
                'name_first' => $data['name_first'] ?? 'Customer',
                'name_last' => $data['name_last'] ?? '',
                'email_address' => $data['email_address'] ?? 'customer@example.com',
                'cell_number' => $data['cell_number'] ?? '',
                'm_payment_id' => $data['m_payment_id'] ?? uniqid(),
                'amount' => number_format($data['amount'], 2, '.', ''),
                'item_name' => $data['item_name'],
                'item_description' => $data['item_description'] ?? $data['item_name'],
                'custom_str1' => $data['custom_str1'] ?? '',
                'custom_str2' => $data['custom_str2'] ?? '',
                'custom_str3' => $data['custom_str3'] ?? '',
                'custom_str4' => $data['custom_str4'] ?? '',
                'custom_str5' => $data['custom_str5'] ?? '',
                'custom_int1' => $data['custom_int1'] ?? 0,
                'custom_int2' => $data['custom_int2'] ?? 0,
                'custom_int3' => $data['custom_int3'] ?? 0,
                'custom_int4' => $data['custom_int4'] ?? 0,
                'custom_int5' => $data['custom_int5'] ?? 0,
            ];

            // Generate PayFast signature
            $pfOutputString = '';
            $pfDataArray = array();
            
            // Define the order of parameters for the signature string
            $pfParameterOrder = [
                'merchant_id', 'merchant_key', 'return_url', 'cancel_url', 'notify_url',
                'name_first', 'name_last', 'email_address', 'cell_number',
                'm_payment_id', 'amount', 'item_name', 'item_description',
                'custom_str1', 'custom_str2', 'custom_str3', 'custom_str4', 'custom_str5',
                'custom_int1', 'custom_int2', 'custom_int3', 'custom_int4', 'custom_int5'
            ];
            
            foreach ($pfParameterOrder as $parameter) {
                if (isset($paymentData[$parameter]) && $paymentData[$parameter] !== '') {
                    $pfDataArray[] = urlencode($parameter) . '=' . urlencode($paymentData[$parameter]);
                }
            }
            
            $pfOutputString = implode('&', $pfDataArray);
            $passphrase = $this->config['passphrase'];
            
            if (!empty($passphrase)) {
                $pfOutputString .= '&passphrase=' . urlencode($passphrase);
            }
            
            $signature = md5($pfOutputString);
            $paymentData['signature'] = $signature;

            // In a real implementation, you would redirect the user to PayFast with this form data
            // For demonstration, we'll simulate the response
            
            return new PaymentResponse(
                false, // PayFast requires redirect to their payment page
                $paymentData['m_payment_id'],
                [
                    'gatewayTransactionId' => $paymentData['m_payment_id'],
                    'data' => [
                        'merchant_id' => $this->config['merchant_id'],
                        'm_payment_id' => $paymentData['m_payment_id'],
                        'amount' => $data['amount'],
                        'currency' => 'ZAR',
                        'item_name' => $data['item_name'],
                        'signature' => $signature,
                        'payment_url' => $this->getPayFastEndpoint(), // URL to POST the form data to
                        'form_fields' => $paymentData, // All fields to be submitted in form
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

            // Prepare refund request data for PayFast
            $refundData = [
                'merchant_id' => $this->config['merchant_id'],
                'merchant_key' => $this->config['merchant_key'],
                'm_payment_id' => $transactionId,
                'amount' => $amount !== null ? number_format($amount, 2, '.', '') : null,
                'currency' => 'ZAR',
                'refund_reason' => $data['refund_reason'] ?? 'Customer request',
                'reference' => 'REF_' . uniqid(),
            ];

            // Remove null values
            $refundData = array_filter($refundData);

            // Generate PayFast signature for refund
            $pfOutputString = '';
            $pfDataArray = array();
            
            // Define the order of parameters for the signature string
            $pfParameterOrder = [
                'merchant_id', 'merchant_key', 'm_payment_id', 'amount', 'currency',
                'refund_reason', 'reference'
            ];
            
            foreach ($pfParameterOrder as $parameter) {
                if (isset($refundData[$parameter]) && $refundData[$parameter] !== '') {
                    $pfDataArray[] = urlencode($parameter) . '=' . urlencode($refundData[$parameter]);
                }
            }
            
            $pfOutputString = implode('&', $pfDataArray);
            $passphrase = $this->config['passphrase'];
            
            if (!empty($passphrase)) {
                $pfOutputString .= '&passphrase=' . urlencode($passphrase);
            }
            
            $signature = md5($pfOutputString);
            $refundData['signature'] = $signature;

            // In a real implementation, you would make an API request to PayFast's refund endpoint
            // For demonstration, we'll simulate a successful refund
            
            return new PaymentResponse(
                true,
                $refundData['reference'],
                [
                    'gatewayTransactionId' => $refundData['reference'],
                    'data' => [
                        'merchant_id' => $this->config['merchant_id'],
                        'm_payment_id' => $transactionId,
                        'reference' => $refundData['reference'],
                        'amount' => $amount ?? 0,
                        'currency' => 'ZAR',
                        'refund_reason' => $refundData['refund_reason'] ?? 'Customer request',
                        'status' => 'COMPLETED',
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
     * Cancel a payment
     *
     * Note: PayFast doesn't support direct cancellation of processed payments.
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
                    'errorMessage' => 'Direct cancellation is not supported for PayFast. Please use refund for settled transactions.',
                    'gatewayTransactionId' => $transactionId,
                    'data' => [
                        'suggestion' => 'Use refund method instead of cancel for PayFast gateway.',
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
     * Note: PayFast gateway primarily handles one-time payments.
     * Recurring payments would need custom implementation or use of PayFast's subscription products.
     *
     * @param array $data Subscription data
     * @return Subscription
     */
    public function subscribe(array $data): \ShamimStack\AllInOnePayment\Models\Subscription
    {
        // PayFast doesn't natively support subscriptions through a simple API.
        // For recurring payments, PayFast offers subscription products or you would need to:
        // 1. Implement recurring billing logic on your side
        // 2. Use PayFast's subscription API if available
        // 3. Store customer payment details securely (PCI-DSS compliant)
        
        return new \ShamimStack\AllInOnePayment\Models\Subscription([
            'gateway' => 'payfast',
            'gateway_subscription_id' => 'sub_payfast_' . uniqid(),
            'status' => 'active',
            'plan_id' => $data['plan_id'] ?? null,
            'customer_id' => $data['customer_email'] ?? null,
            'start_date' => now(),
            'end_date' => null,
            'data' => [
                'note' => 'PayFast gateway does not natively support subscriptions via standard API. Recurring payments require PayFast subscription products or custom implementation.',
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
            $payload = $request->all(); // PayFast sends data as POST parameters
            
            if (empty($payload)) {
                return false;
            }
            
            # Verify the PayFast signature
            $signature = $payload['signature'] ?? '';
            
            if (empty($signature)) {
                return false;
            }
            
            # Remove signature from params for verification
            $paramsWithoutSignature = $payload;
            unset($paramsWithoutSignature['signature']);
            
            # Generate the signature string
            $pfOutputString = '';
            $pfDataArray = array();
            
            # Define the order of parameters for the signature string
            $pfParameterOrder = [
                'merchant_id', 'merchant_key', 'return_url', 'cancel_url', 'notify_url',
                'name_first', 'name_last', 'email_address', 'cell_number',
                'm_payment_id', 'amount', 'item_name', 'item_description',
                'custom_str1', 'custom_str2', 'custom_str3', 'custom_str4', 'custom_str5',
                'custom_int1', 'custom_int2', 'custom_int3', 'custom_int4', 'custom_int5',
                'payment_status', 'pm_ref_no', 'extra1', 'extra2', 'extra3'
            ];
            
            foreach ($pfParameterOrder as $parameter) {
                if (isset($paramsWithoutSignature[$parameter]) && $paramsWithoutSignature[$parameter] !== '') {
                    $pfDataArray[] = urlencode($parameter) . '=' . urlencode($paramsWithoutSignature[$parameter]);
                }
            }
            
            $pfOutputString = implode('&', $pfDataArray);
            $passphrase = $this->config['passphrase'];
            
            if (!empty($passphrase)) {
                $pfOutputString .= '&passphrase=' . urlencode($passphrase);
            }
            
            $expectedSignature = md5($pfOutputString);
            
            # Verify signature
            if (!hash_equals($expectedSignature, $signature)) {
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
        return 'payfast';
    }

    /**
     * Get the PayFast endpoint
     *
     * @return string
     */
    protected function getPayFastEndpoint(): string
    {
        if ($this->config['mode'] === 'live') {
            return 'https://www.payfast.co.za/eng/process';
        } else {
            return 'https://sandbox.payfast.co.za/eng/process';
        }
    }
}