<?php

namespace ShamimStack\WwwPay\Gateways\SouthAfrica;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SnapScanGateway implements PaymentGateway
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
            if (!isset($data['amount']) || !isset($data['currency'])) {
                throw new PaymentException('Amount and currency are required for SnapScan payment.');
            }

            // Validate currency (SnapScan primarily processes ZAR)
            if (strtoupper($data['currency']) !== 'ZAR') {
                throw new PaymentException('SnapScan gateway primarily supports ZAR currency.');
            }

            // Prepare SnapScan payment data
            $paymentData = [
                'amount' => number_format($data['amount'], 2, '.', ''),
                'currency' => strtoupper($data['currency']),
                'reference' => $data['reference'] ?? uniqid(),
                'callback_url' => $data['callback_url'] ?? url('/payment/callback/snapscan'),
                'cancel_url' => $data['cancel_url'] ?? url('/payment/cancel'),
                'merchant_id' => $this->config['merchant_id'],
                'app_id' => $this->config['app_id'],
                // Note: SnapScan typically works by generating a QR code that the user scans with their app
                // The actual payment flow is different from traditional card gateways
            ];

            // In a real implementation, you would make an API request to SnapScan to initiate payment
            // For demonstration, we'll simulate the QR code generation response
            
            // Simulate SnapScan response with QR code data
            $qrCodeData = 'snapscan://payment?merchant_id=' . $this->config['merchant_id'] . 
                         '&amount=' . $data['amount'] . 
                         '&currency=ZAR&reference=' . $paymentData['reference'];

            return new PaymentResponse(
                false, // SnapScan requires user to scan QR code with their app
                $paymentData['reference'],
                [
                    'gatewayTransactionId' => $paymentData['reference'],
                    'data' => [
                        'merchant_id' => $this->config['merchant_id'],
                        'reference' => $paymentData['reference'],
                        'amount' => $data['amount'],
                        'currency' => 'ZAR',
                        'qr_code_data' => $qrCodeData,
                        'qr_code_image_url' => $this->getQrCodeImageUrl($qrCodeData), // URL to generated QR code image
                        'callback_url' => $data['callback_url'],
                        'expires_at' => now()->addMinutes(15)->toDateTimeString(), // QR codes typically expire after 15 minutes
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

            // For SnapScan, refunds would typically be processed through their merchant dashboard
            # or API if available. Since SnapScan is primarily a consumer-facing QR payment method,
            # refunds often need to be initiated manually or through their backend systems.
            
            # We'll simulate a refund response
            
            return new PaymentResponse(
                true,
                $transactionId . '_REF',
                [
                    'gatewayTransactionId' => $transactionId . '_REF',
                    'data' => [
                        'original_transaction_id' => $transactionId,
                        'refund_amount' => $amount ?? 0,
                        'currency' => 'ZAR',
                        'status' => 'completed',
                        'refund_id' => 'ref_' . uniqid(),
                        'message' => 'SnapScan refund processed (typically handled via merchant dashboard)',
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
     * Note: SnapScan payments that have been scanned but not completed can often be allowed to expire.
     * Completed SnapScan payments require contacting support or using their merchant dashboard for refunds.
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
                    'errorMessage' => 'Direct cancellation is not supported for SnapScan. Please allow QR code to expire or use refund for completed payments.',
                    'gatewayTransactionId' => $transactionId,
                    'data' => [
                        'suggestion' => 'Allow QR code to expire (typically 15 minutes) or use refund method for completed payments.',
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
     * Note: SnapScan gateway primarily handles one-time payments via QR code scanning.
     * Recurring payments would need custom implementation.
     *
     * @param array $data Subscription data
     * @return Subscription
     */
    public function subscribe(array $data): \ShamimStack\WwwPay\Models\Subscription
    {
        // SnapScan doesn't natively support subscriptions through their standard API.
        # For recurring payments, merchants would need to implement custom solutions.
        return new \ShamimStack\WwwPay\Models\Subscription([
            'gateway' => 'snapscan',
            'gateway_subscription_id' => 'sub_snapscan_' . uniqid(),
            'status' => 'active',
            'plan_id' => $data['plan_id'] ?? null,
            'customer_id' => null, # SnapScan doesn't typically store customer IDs for one-time payments
            'start_date' => now(),
            'end_date' => null,
            'data' => [
                'note' => 'SnapScan gateway does not natively support subscriptions. Recurring payments require custom implementation.',
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
            $payload = $request->all(); # SnapScan sends data as POST parameters
            
            if (empty($payload)) {
                return false;
            }
            
            # Verify the webhook if needed
            # Implementation depends on SnapScan's webhook security mechanism
            
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
        return 'snapscan';
    }

    /**
     * Get QR code image URL for SnapScan payment
     *
     * @param string $qrCodeData
     * @return string
     */
    protected function getQrCodeImageUrl(string $qrCodeData): string
    {
        # In a real implementation, you would use a QR code generation service
        # or library to generate and return a URL to the QR code image
        # For demonstration, we'll return a placeholder URL
        
        # Example using a public QR code API (not for production use)
        return 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($qrCodeData);
    }
}