<?php

namespace ShamimStack\WwwPay\Gateways\India;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaytmGateway implements PaymentGateway
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
            if (!isset($data['amount']) || !isset($data['currency']) || !isset($data['customer_id'])) {
                throw new PaymentException('Amount, currency, and customer_id are required for Paytm payment.');
            }

            // Validate currency (Paytm primarily processes INR)
            if (strtoupper($data['currency']) !== 'INR') {
                throw new PaymentException('Paytm gateway primarily supports INR currency.');
            }

            // Prepare Paytm payment data
            $orderId = $data['order_id'] ?? uniqid();
            $mid = $this->config['merchant_id'];
            $website = $this->config['website'] ?? 'WEBSTAGING';
            $industryTypeId = $data['industry_type_id'] ?? 'Retail';
            $channelId = $data['channel_id'] ?? 'WEB';
            $txnAmount = number_format($data['amount'], 2, '.', '');
            $callbackUrl = $data['callback_url'] ?? url('/payment/callback/paytm');

            // Prepare parameters array
            $paramList = [
                'MID' => $mid,
                'WEBSITE' => $website,
                'INDUSTRY_TYPE_ID' => $industryTypeId,
                'CHANNEL_ID' => $channelId,
                'ORDER_ID' => $orderId,
                'CUST_ID' => $data['customer_id'],
                'TXN_AMOUNT' => $txnAmount,
                'CALLBACK_URL' => $callbackUrl,
            ];

            // Add optional parameters if provided
            if (!empty($data['customer_name'])) {
                $paramList['CUST_NAME'] = $data['customer_name'];
            }
            if (!empty($data['customer_email'])) {
                $paramList['CUST_EMAIL'] = $data['customer_email'];
            }
            if (!empty($data['customer_mobile'])) {
                $paramList['CUST_MOBILE'] = $data['customer_mobile'];
            }

            // Generate checksum
            $checksum = $this->getChecksum($paramList, $this->config['merchant_key']);
            $paramList['CHECKSUMHASH'] = $checksum;

            // Prepare HTML form for redirect to Paytm (in real implementation, you'd return this form)
            // For API-based integration, you would make an HTTP request to Paytm's API endpoint
            // Since Paytm's API varies based on their integration method (SDK, API, etc.),
            // we'll simulate the response for demonstration
            
            // In a real implementation, you would either:
            // 1. Return an HTML form that auto-submits to Paytm's payment gateway
            // 2. Use Paytm's API to initiate transaction and get a transaction token
            
            // Simulate successful payment initiation
            return new PaymentResponse(
                false, // Paytm typically redirects user to complete payment
                $orderId,
                [
                    'gatewayTransactionId' => $orderId,
                    'data' => [
                        'order_id' => $orderId,
                        'mid' => $mid,
                        'website' => $website,
                        'order_amount' => $txnAmount,
                        'currency' => 'INR',
                        'customer_id' => $data['customer_id'],
                        'callback_url' => $callbackUrl,
                        'checksum' => $checksum,
                        'status' => 'TXN_SUCCESS', // This would be updated after payment completion
                        'txn_token' => ' simulated_token_for_demo ', // In real implementation, you'd get this from Paytm API
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
            // Validate transaction ID (orderId in Paytm context)
            if (empty($transactionId)) {
                throw new PaymentException('Transaction ID is required for refund.');
            }

            // Prepare Paytm refund data
            $mid = $this->config['merchant_id'];
            $orderId = $transactionId; // In Paytm, transactionId is usually the orderId
            $txnType = 'REFUND';
            $txnAmount = $amount !== null ? number_format($amount, 2, '.', '') : null; // Amount in INR

            $refundId = 'REF' . uniqid(); // Unique refund ID

            $paramList = [
                'MID' => $mid,
                'ORDER_ID' => $orderId,
                'TXN_TYPE' => $txnType,
                'REFUNDID' => $refundId,
            ];

            if ($txnAmount !== null) {
                $paramList['REFUNDAMOUNT'] = $txnAmount;
            }

            // Generate checksum for refund
            $checksum = $this->getChecksum($paramList, $this->config['merchant_key']);
            $paramList['CHECKSUMHASH'] = $checksum;

            // In a real implementation, you would make an API call to Paytm's refund endpoint
            // For demonstration, we'll simulate a successful refund
            
            return new PaymentResponse(
                true,
                $refundId,
                [
                    'gatewayTransactionId' => $refundId,
                    'data' => [
                        'mid' => $mid,
                        'order_id' => $orderId,
                        'refund_id' => $refundId,
                        'txn_type' => $txnType,
                        'refund_amount' => $txnAmount ?? '0.00',
                        'currency' => 'INR',
                        'status' => 'TXN_SUCCESS',
                        'msg' => 'Refund successful',
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
     * Note: Paytm doesn't support direct cancellation of processed payments.
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
                    'errorMessage' => 'Direct cancellation is not supported for Paytm. Please use refund for settled transactions.',
                    'gatewayTransactionId' => $transactionId,
                    'data' => [
                        'suggestion' => 'Use refund method instead of cancel for Paytm gateway.',
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
     * Note: Paytm gateway primarily handles one-time payments.
     * Recurring payments would need custom implementation or use of Paytm's subscription products.
     *
     * @param array $data Subscription data
     * @return Subscription
     */
    public function subscribe(array $data): \ShamimStack\WwwPay\Models\Subscription
    {
        // Paytm doesn't natively support subscriptions through a simple API like card gateways.
        // For recurring payments, Paytm offers specific subscription products or you would need to:
        // 1. Store customer payment credentials securely (PCI-DSS compliant)
        // 2. Initiate new payments on schedule
        // 3. Handle failed payments and retries
        
        return new \ShamimStack\WwwPay\Models\Subscription([
            'gateway' => 'paytm',
            'gateway_subscription_id' => 'sub_paytm_' . uniqid(),
            'status' => 'active',
            'plan_id' => $data['plan_id'] ?? null,
            'customer_id' => $data['customer_id'] ?? null,
            'start_date' => now(),
            'end_date' => null,
            'data' => [
                'note' => 'Paytm gateway does not natively support subscriptions via standard API. Recurring payments require Paytm subscription products or custom implementation.',
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
            
            // Paytm sends webhook data as form parameters
            $input = $request->all();
            
            if (empty($input)) {
                // Try to parse as JSON if form data is empty
                $input = json_decode($payload, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return false;
                }
            }
            
            # Verify the checksum if present
            if (!empty($input['CHECKSUMHASH']) && !empty($this->config['merchant_key'])) {
                $isValidChecksum = $this->verifyChecksum($input, $this->config['merchant_key']);
                if (!$isValidChecksum) {
                    # Invalid checksum
                    return false;
                }
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
        return 'paytm';
    }

    /**
     * Generate Paytm checksum
     *
     * @param array $params
     * @param string $key
     * @return string
     */
    protected function getChecksum(array $params, string $key): string
    {
        // Paytm checksum generation logic
        // This is a simplified version - in practice, you'd use Paytm's checksum library
        
        // Sort parameters by key
        ksort($params);
        
        // Create string in format: key1=value1&key2=value2&...
        $string = '';
        foreach ($params as $key => $value) {
            if ($key !== 'CHECKSUMHASH') { // Exclude checksum itself
                $string .= $key . '=' . $value . '&';
            }
        }
        
        // Remove trailing &
        $string = rtrim($string, '&');
        
        // Append merchant key
        $string .= $key;
        
        // Calculate SHA256 hash
        $hash = hash('sha256', $string);
        
        return $hash;
    }

    /**
     * Verify Paytm checksum
     *
     * @param array $params
     * @param string $key
     * @return bool
     */
    protected function verifyChecksum(array $params, string $key): bool
    {
        // Extract checksum
        $checksum = $params['CHECKSUMHASH'] ?? '';
        
        // Remove checksum from params for verification
        $paramsWithoutChecksum = $params;
        unset($paramsWithoutChecksum['CHECKSUMHASH']);
        
        # Generate checksum with the params
        $generatedChecksum = $this->getChecksum($paramsWithoutChecksum, $key);
        
        # Compare
        return hash_equals($generatedChecksum, $checksum);
    }
}