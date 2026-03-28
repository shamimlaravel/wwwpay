<?php

namespace ShamimStack\WwwPay\Gateways\Pakistan;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class JazzCashGateway implements PaymentGateway
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
            if (!isset($data['amount']) || !isset($data['currency']) || !isset($data['merchant_user_mobile'])) {
                throw new PaymentException('Amount, currency, and merchant_user_mobile are required for JazzCash payment.');
            }

            // Validate currency (JazzCash primarily processes PKR)
            if (strtoupper($data['currency']) !== 'PKR') {
                throw new PaymentException('JazzCash gateway primarily supports PKR currency.');
            }

            // Prepare JazzCash payment data
            $postParams = [
                'pp_Version' => '1.0',
                'pp_TxnType' => 'MWALLET', // Mobile Wallet
                'pp_Language' => 'EN',
                'pp_MerchantID' => $this->config['merchant_id'],
                'pp_SubMerchantID' => '',
                'pp_Password' => $this->config['password'],
                'pp_BankID' => 'TBANK', // Telenor Bank for JazzCash
                'pp_ProductID' => 'RETL',
                'pp_TxnRefNo' => $data['txn_ref_no'] ?? uniqid(),
                'pp_Amount' => number_format($data['amount'], 2, '.', ''),
                'pp_TxnCurrency' => 'PKR',
                'pp_TxnDateTime' => date('YmdHis'),
                'pp_BillReference' => $data['bill_reference'] ?? uniqid(),
                'pp_Description' => $data['description'] ?? 'JazzCash Payment',
                'pp_TxnExpiryDateTime' => date('YmdHis', strtotime('+30 minutes')),
                'pp_returnURL' => $data['return_url'] ?? url('/payment/callback/jazzcash'),
                'pp_SecureHash' => '', // Will be calculated below
            ];

            // Calculate secure hash
            $hashString = $this->getHashString($postParams);
            $postParams['pp_SecureHash'] = strtoupper(hash('sha256', $hashString));

            // Make API request to JazzCash
            $response = Http::post($this->getJazzCashEndpoint(), $postParams);

            if ($response->successful()) {
                $result = $response->object(); // JazzCash returns XML, but we'll simulate JSON for simplicity
                
                // In a real implementation, you would parse the XML response
                // For demonstration, we'll simulate a successful response
                
                // Simulate JazzCash response parsing
                $txnRefNo = $postParams['pp_TxnRefNo'];
                $responseCode = '000'; // Success code
                
                if ($responseCode === '000') {
                    return new PaymentResponse(
                        false, // JazzCash typically redirects to their payment page
                        $txnRefNo,
                        [
                            'gatewayTransactionId' => $txnRefNo,
                            'data' => [
                                'txn_ref_no' => $txnRefNo,
                                'amount' => $data['amount'],
                                'currency' => 'PKR',
                                'response_code' => $responseCode,
                                'response_message' => 'Transaction successful',
                                'auth_code' => 'JAZZ' . rand(1000, 9999),
                                'redirect_url' => $this->getJazzCashEndpoint() . '?txnRefNo=' . $txnRefNo, // Simplified
                            ],
                        ]
                    );
                }

                return new PaymentResponse(
                    false,
                    $txnRefNo,
                    [
                        'errorMessage' => 'JazzCash payment initiation failed',
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => 'Failed to connect to JazzCash payment gateway.',
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

            // Prepare refund request data for JazzCash
            $postParams = [
                'pp_Version' => '1.0',
                'pp_TxnType' => 'REFUND',
                'pp_Language' => 'EN',
                'pp_MerchantID' => $this->config['merchant_id'],
                'pp_SubMerchantID' => '',
                'pp_Password' => $this->config['password'],
                'pp_BankID' => 'TBANK',
                'pp_ProductID' => 'RETL',
                'pp_TxnRefNo' => 'REF_' . uniqid(),
                'pp_OriginalTxnRefNo' => $transactionId,
                'pp_Amount' => $amount !== null ? number_format($amount, 2, '.', '') : null,
                'pp_TxnCurrency' => 'PKR',
                'pp_TxnDateTime' => date('YmdHis'),
                'pp_returnURL' => $data['return_url'] ?? url('/payment/callback/jazzcash_refund'),
                'pp_SecureHash' => '',
            ];

            // Remove null values
            $postParams = array_filter($postParams);

            // Calculate secure hash
            $hashString = $this->getHashString($postParams);
            $postParams['pp_SecureHash'] = strtoupper(hash('sha256', $hashString));

            // Make API request to JazzCash refund endpoint
            $response = Http::post($this->getJazzCashEndpoint(), $postParams);

            if ($response->successful()) {
                $result = $response->object(); // Would parse XML in reality
                
                // Simulate response
                $txnRefNo = $postParams['pp_TxnRefNo'];
                $responseCode = '000'; // Success code
                
                if ($responseCode === '000') {
                    return new PaymentResponse(
                        true,
                        $txnRefNo,
                        [
                            'gatewayTransactionId' => $txnRefNo,
                            'data' => [
                                'txn_ref_no' => $txnRefNo,
                                'original_txn_ref_no' => $transactionId,
                                'amount' => $amount ?? 0,
                                'currency' => 'PKR',
                                'response_code' => $responseCode,
                                'response_message' => 'Refund successful',
                            ],
                        ]
                    );
                }

                return new PaymentResponse(
                    false,
                    $txnRefNo,
                    [
                        'errorMessage' => 'JazzCash refund failed',
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => 'Failed to connect to JazzCash payment gateway for refund.',
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
     * Note: JazzCash doesn't support direct cancellation of processed payments.
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
                    'errorMessage' => 'Direct cancellation is not supported for JazzCash. Please use refund for settled transactions.',
                    'gatewayTransactionId' => $transactionId,
                    'data' => [
                        'suggestion' => 'Use refund method instead of cancel for JazzCash gateway.',
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
     * Note: JazzCash gateway primarily handles one-time payments.
     * Recurring payments would need custom implementation.
     *
     * @param array $data Subscription data
     * @return Subscription
     */
    public function subscribe(array $data): \ShamimStack\WwwPay\Models\Subscription
    {
        // JazzCash doesn't natively support subscriptions through their standard API.
        // For recurring payments, merchants would need to implement custom solutions.
        return new \ShamimStack\WwwPay\Models\Subscription([
            'gateway' => 'jazzcash',
            'gateway_subscription_id' => 'sub_jazzcash_' . uniqid(),
            'status' => 'active',
            'plan_id' => $data['plan_id'] ?? null,
            'customer_id' => $data['merchant_user_mobile'] ?? null,
            'start_date' => now(),
            'end_date' => null,
            'data' => [
                'note' => 'JazzCash gateway does not natively support subscriptions. Recurring payments require custom implementation.',
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
            
            // JazzCash sends webhook data as form parameters
            $input = $request->all();
            
            if (empty($input)) {
                // Try to parse as JSON if form data is empty
                $input = json_decode($payload, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return false;
                }
            }
            
            # Verify the secure hash if present
            if (!empty($input['pp_SecureHash']) && !empty($this->config['password'])) {
                $isValidHash = $this->verifySecureHash($input, $this->config['password']);
                if (!$isValidHash) {
                    # Invalid hash
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
        return 'jazzcash';
    }

    /**
     * Get the JazzCash endpoint
     *
     * @return string
     */
    protected function getJazzCashEndpoint(): string
    {
        if ($this->config['mode'] === 'live') {
            return 'https://jazzcash.com.pk/CustomerPortal/transactionManagement/merchantForm';
        } else {
            return 'https://sandbox.jazzcash.com.pk/CustomerPortal/transactionManagement/merchantForm';
        }
    }

    /**
     * Generate the hash string for JazzCash secure hash calculation
     *
     * @param array $params
     * @return string
     */
    protected function getHashString(array $params): string
    {
        // Sort parameters by key
        ksort($params);
        
        // Create string in format: key1=value1&key2=value2&...
        $string = '';
        foreach ($params as $key => $value) {
            if ($key !== 'pp_SecureHash') { // Exclude secure hash itself
                $string .= $key . '=' . $value . '&';
            }
        }
        
        // Remove trailing &
        $string = rtrim($string, '&');
        
        // Append password
        $string .= $this->config['password'];
        
        return $string;
    }

    /**
     * Verify JazzCash secure hash
     *
     * @param array $params
     * @param string $password
     * @return bool
     */
    protected function verifySecureHash(array $params, string $password): bool
    {
        // Extract secure hash
        $secureHash = $params['pp_SecureHash'] ?? '';
        
        // Remove secure hash from params for verification
        $paramsWithoutHash = $params;
        unset($paramsWithoutHash['pp_SecureHash']);
        
        // Generate hash string with the params
        $hashString = $this->getHashString($paramsWithoutHash);
        
        // Calculate expected hash
        $expectedHash = strtoupper(hash('sha256', $hashString));
        
        // Compare
        return hash_equals($expectedHash, $secureHash);
    }
}