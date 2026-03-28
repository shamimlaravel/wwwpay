<?php

namespace ShamimStack\AllInOnePayment\Gateways\Global;

use ShamimStack\AllInOnePayment\Contracts\PaymentGateway;
use ShamimStack\AllInOnePayment\Contracts\PaymentResponse;
use ShamimStack\AllInOnePayment\Exceptions\PaymentException;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\RedirectUrls;

class PayPalGateway implements PaymentGateway
{
    /**
     * @var ApiContext
     */
    protected $apiContext;

    /**
     * Create a new gateway instance.
     *
     * @param  array  $config
     * @return void
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->apiContext = new ApiContext(
            new OAuthTokenCredential(
                $config['client_id'] ?? '',
                $config['client_secret'] ?? ''
            )
        );

        $this->apiContext->setConfig(
            ['mode' => $config['mode'] ?? 'sandbox']
        );
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
            if (!isset($data['amount']) || !isset($data['currency']) || !isset($data['return_url']) || !isset($data['cancel_url'])) {
                throw new PaymentException('Amount, currency, return_url, and cancel_url are required for PayPal payment.');
            }

            $payer = new Payer();
            $payer->setPaymentMethod('paypal');

            $amount = new Amount();
            $amount->setTotal($data['amount']);
            $amount->setCurrency($data['currency']);

            $transaction = new Transaction();
            $transaction->setAmount($amount)
                ->setDescription($data['description'] ?? 'Payment');

            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl($data['return_url'])
                ->setCancelUrl($data['cancel_url']);

            $payment = new Payment();
            $payment->setIntent('sale')
                ->setPayer($payer)
                ->setRedirectUrls($redirectUrls)
                ->setTransactions(array($transaction));

            // Create payment
            $payment->create($this->apiContext);

            // Get approval URL
            $approvalUrl = $payment->getApprovalLink();

            return new PaymentResponse(
                false, // Not yet successful, needs user approval
                $payment->getId(),
                [
                    'gatewayTransactionId' => $payment->getId(),
                    'data' => [
                        'approval_url' => $approvalUrl,
                        'payment_id' => $payment->getId(),
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
     * Note: PayPal refunds are done on a sale transaction, which requires capturing the payment first.
     * This method assumes the payment has been captured (completed).
     *
     * @param string $transactionId Payment ID (not the sale ID)
     * @param float|null $amount Amount to refund (null for full refund)
     * @return PaymentResponse
     */
    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        try {
            // Get the payment details
            $payment = Payment::get($transactionId, $this->apiContext);

            // We need to find the sale transaction ID from the payment
            // This is a simplified version; in reality, you might need to capture first.
            // For the sake of example, we assume the first transaction is a sale and we can refund it.
            $transactions = $payment->getTransactions();
            if (empty($transactions)) {
                throw new PaymentException('No transactions found for this payment.');
            }

            // In a real implementation, you would need to capture the payment first to get a sale ID.
            // This example is simplified and may not work without capturing.
            // We'll return a response indicating that capture is needed first.
            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => 'Refund requires the payment to be captured first. Please capture the payment before refunding.',
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
     * @param string $transactionId Payment ID to cancel
     * @return PaymentResponse
     */
    public function cancel(string $transactionId): PaymentResponse
    {
        try {
            // For PayPal, canceling a payment is only possible if it's in a state that allows it (e.g., created but not approved).
            // We'll attempt to void the payment if possible, but note that PayPal doesn't have a direct void for unexecuted payments.
            // Instead, we can just not execute the payment (by not redirecting the user to approve).
            // However, for the sake of the API, we'll return a response indicating that the payment was not executed.
            // In a real implementation, you might want to delete the payment if it's still in the 'created' state.
            $payment = Payment::get($transactionId, $this->apiContext);

            if ($payment->getState() == 'created') {
                // Payment is created but not approved, we can consider it canceled.
                return new PaymentResponse(
                    true,
                    $transactionId,
                    [
                        'gatewayTransactionId' => $transactionId,
                        'data' => ['state' => $payment->getState()],
                    ]
                );
            } else {
                return new PaymentResponse(
                    false,
                    $transactionId,
                    [
                        'errorMessage' => 'Payment cannot be canceled because it is in state: ' . $payment->getState(),
                    ]
                );
            }
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
     * Note: PayPal subscriptions are handled via PayPal Billing Plans and Agreements.
     * This is a simplified example and does not implement the full subscription flow.
     *
     * @param array $data Subscription data
     * @return Subscription
     */
    public function subscribe(array $data): \ShamimStack\AllInOnePayment\Models\Subscription
    {
        // This is a placeholder. In a real implementation, you would:
        // 1. Create a billing plan
        // 2. Activate the billing plan
        // 3. Create a billing agreement
        // 4. Execute the agreement
        // For now, we return a basic subscription model.
        return new \ShamimStack\AllInOnePayment\Models\Subscription([
            'gateway' => 'paypal',
            'gateway_subscription_id' => 'sub_' . uniqid(),
            'status' => 'active',
            'plan_id' => $data['plan_id'] ?? null,
            'customer_id' => $data['customer_id'] ?? null,
            'start_date' => now(),
            'end_date' => null, // For ongoing subscriptions
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
        // PayPal webhook verification is more complex and requires validating the transmission ID, timestamp, etc.
        // For brevity, we'll return true in this example, but in production you must verify the webhook.
        // See: https://developer.paypal.com/docs/api/notifications/webhooks/#verify_webhook
        $payload = $request->getContent();
        $headers = $request->headers->all();

        // In a real implementation, you would verify the webhook signature using PayPal's SDK.
        // We'll skip the verification for this example and assume it's valid.
        // Note: This is not secure and should not be used in production.

        // Parse the payload
        $event = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        // Handle the event (simplified)
        switch ($event['event_type']) {
            case 'PAYMENT.SALE.COMPLETED':
                // Handle completed payment
                break;
            case 'PAYMENT.SALE.DENIED':
                // Handle denied payment
                break;
            // Add more event types as needed
            default:
                // Unknown event type
                break;
        }

        return true;
    }

    /**
     * Get gateway name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'paypal';
    }
}