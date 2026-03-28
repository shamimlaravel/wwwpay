<?php

namespace ShamimStack\WwwPay\Gateways\India;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class UpiGateway implements PaymentGateway
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
            if (!isset($data['amount']) || !isset($data['currency']) || !isset($data['upi_id'])) {
                throw new PaymentException('Amount, currency, and upi_id are required for UPI payment.');
            }

            // Validate currency (UPI primarily processes INR)
            if (strtoupper($data['currency']) !== 'INR') {
                throw new PaymentException('UPI gateway primarily supports INR currency.');
            }

            // Prepare UPI payment data
            // Note: UPI implementations vary by bank/provider. This is a simplified example.
            // In practice, you might integrate with a UPI aggregator like Razorpay, Paytm, PhonePe, etc.
            // or use bank-specific UPI APIs.
            
            $upiData = [
                'merchant_id' => $this->config['merchant_id'],
                'merchant_key' => $this->config['merchant_key'],
                'amount' => number_format($data['amount'], 2, '.', ''),
                'currency' => 'INR',
                'upi_id' => $data['upi_id'],
                'transaction_id' => $data['transaction_id'] ?? uniqid(),
                'transaction_note' => $data['description'] ?? 'UPI Payment',
                'callback_url' => $data['callback_url'] ?? url('/payment/callback/upi'),
            ];

            // Generate checksum/signature if required by your UPI provider
            // This is provider-specific and would depend on your UPI integration method
            
            // For demonstration, we'll simulate a UPI payment initiation
            // In a real implementation, you would make an API call to your UPI provider
            
            // Simulate successful UPI payment initiation
            return new PaymentResponse(
                false, // UPI typically requires user to complete payment via their UPI app
                $upiData['transaction_id'],
                [
                    'gatewayTransactionId' => $upiData['transaction_id'],
                    'data' => [
                        'upi_id' => $upiData['upi_id'],
                        'amount' => $upiData['amount'],
                        'currency' => $upiData['currency'],
                        'transaction_note' => $upiData['transaction_note'],
                        'upi_link' => 'upi://pay?pa=' . $data['upi_id'] . 
                                      '&pn=Merchant&am=' . $data['amount'] . 
                                      '&cu=INR&tn=' . urlencode($data['description'] ?? 'Payment'),
                        'status' => 'initiated',
                        'expires_at' => now()->addMinutes(30)->toDateTimeString(),
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

            // For UPI, refunds are typically processed through the same UPI infrastructure
            // This would involve calling your UPI provider's refund API
            
            // Simulate UPI refund
            return new PaymentResponse(
                true,
                $transactionId . '_REF' . uniqid(),
                [
                    'gatewayTransactionId' => $transactionId . '_REF' . uniqid(),
                    'data' => [
                        'original_transaction_id' => $transactionId,
                        'refund_amount' => $amount ?? 0,
                        'currency' => 'INR',
                        'status' => 'success',
                        'refund_id' => 'ref_' . uniqid(),
                        'message' => 'UPI refund processed successfully',
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
     * Note: UPI payments that have been initiated but not completed can often be allowed to expire.
     * Completed UPI payments require refunds for reversal.
     *
     * @param string $transactionId Transaction ID to cancel
     * @return PaymentResponse
     */
    public function cancel(string $transactionId): PaymentResponse
    {
        try {
            // For UPI, we cannot cancel a completed payment - only refund
            // For pending payments, we could let them expire or check with the UPI provider
            return new PaymentResponse(
                false,
                $transactionId,
                [
                    'errorMessage' => 'Direct cancellation is not supported for UPI. Please use refund for completed payments.',
                    'gatewayTransactionId' => $transactionId,
                    'data' => [
                        'suggestion' => 'Use refund method instead of cancel for UPI gateway.',
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
     * Note: UPI itself doesn't handle subscriptions directly.
     * Recurring UPI payments would need to be implemented by initiating new UPI collect requests on schedule.
     *
     * @param array $data Subscription data
     * @return Subscription
     */
    public function subscribe(array $data): \ShamimStack\WwwPay\Models\Subscription
    {
        // UPI doesn't natively support subscriptions through a standard API.
        // For recurring payments, merchants would need to:
        // 1. Initiate new UPI collect requests on schedule
        // 2. Handle expired mandates and failed payments
        // 3. Notify customers of upcoming payments
        
        return new \ShamimStack\WwwPay\Models\Subscription([
            'gateway' => 'upi',
            'gateway_subscription_id' => 'sub_upi_' . uniqid(),
            'status' => 'active',
            'plan_id' => $data['plan_id'] ?? null,
            'customer_id' => $data['upi_id'] ?? null,
            'start_date' => now(),
            'end_date' => null,
            'data' => [
                'note' => 'UPI gateway does not natively support subscriptions. Recurring payments require custom implementation.',
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
            
            // UPI webhook format depends on your UPI provider/aggregator
            $input = $request->all();
            
            if (empty($input)) {
                $input = json_decode($payload, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return false;
                }
            }
            
            // Verify the webhook signature if needed
            # This would depend on your specific UPI provider
            
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
        return 'upi';
    }
}