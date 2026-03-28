<?php

namespace ShamimStack\AllInOnePayment\Gateways\Global;

use ShamimStack\AllInOnePayment\Contracts\PaymentGateway;
use ShamimStack\AllInOnePayment\Contracts\PaymentResponse;
use ShamimStack\AllInOnePayment\Exceptions\PaymentException;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;

class StripeGateway implements PaymentGateway
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

        if (!empty($this->config['api_secret'])) {
            Stripe::setApiKey($this->config['api_secret']);
        }
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
            if (!isset($data['amount']) || !isset($data['currency']) || !isset($data['payment_method'])) {
                throw new PaymentException('Amount, currency, and payment_method are required for Stripe payment.');
            }

            // Create a PaymentIntent
            $paymentIntent = PaymentIntent::create([
                'amount' => round($data['amount'] * 100), // Convert to cents
                'currency' => strtolower($data['currency']),
                'payment_method' => $data['payment_method'],
                'confirmation_method' => 'manual',
                'confirm' => true,
                'return_url' => $data['return_url'] ?? null,
            ]);

            // Check if the paymentIntent requires further action
            if ($paymentIntent->status === 'requires_action') {
                // Return response with client secret for 3D Secure or other authentication
                return new PaymentResponse(
                    false, // Not yet successful, requires action
                    $paymentIntent->id,
                    [
                        'gatewayTransactionId' => $paymentIntent->id,
                        'data' => [
                            'client_secret' => $paymentIntent->client_secret,
                            'next_action' => $paymentIntent->next_action,
                            'status' => $paymentIntent->status,
                        ],
                    ]
                );
            }

            // If status is succeeded, return success
            if ($paymentIntent->status === 'succeeded') {
                return new PaymentResponse(
                    true,
                    $paymentIntent->id,
                    [
                        'gatewayTransactionId' => $paymentIntent->id,
                        'data' => [
                            'status' => $paymentIntent->status,
                            'receipt_url' => $paymentIntent->charges->data[0]->receipt_url ?? null,
                        ],
                    ]
                );
            }

            // If we get here, the payment failed
            return new PaymentResponse(
                false,
                $paymentIntent->id,
                [
                    'errorMessage' => 'Payment failed with status: ' . $paymentIntent->status,
                    'gatewayTransactionId' => $paymentIntent->id,
                    'data' => ['status' => $paymentIntent->status],
                ]
            );
        } catch (ApiErrorException $e) {
            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $e->getMessage(),
                    'gatewayTransactionId' => null,
                    'data' => ['stripe_error' => $e->getJsonBody()],
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
            $refundData = ['payment_intent' => $transactionId];

            if ($amount !== null) {
                // Convert amount to cents
                $refundData['amount'] = round($amount * 100);
            }

            $refund = \Stripe\Refund::create($refundData);

            return new PaymentResponse(
                true,
                $refund->id,
                [
                    'gatewayTransactionId' => $refund->id,
                    'data' => [
                        'status' => $refund->status,
                        'amount' => $refund->amount / 100, // Convert back from cents
                        'currency' => $refund->currency,
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
     * @param string $transactionId Transaction ID to cancel
     * @return PaymentResponse
     */
    public function cancel(string $transactionId): PaymentResponse
    {
        try {
            // For Stripe, canceling a payment intent is only possible if it's not yet confirmed or succeeded
            $paymentIntent = PaymentIntent::retrieve($transactionId);
            $paymentIntent->cancel();

            return new PaymentResponse(
                true,
                $paymentIntent->id,
                [
                    'gatewayTransactionId' => $paymentIntent->id,
                    'data' => ['status' => $paymentIntent->status],
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
     * @param array $data Subscription data
     * @return Subscription
     */
    public function subscribe(array $data): \ShamimStack\AllInOnePayment\Models\Subscription
    {
        // For simplicity, we return a basic subscription model.
        // In a real implementation, you would create a subscription in Stripe and return a model.
        return new \ShamimStack\AllInOnePayment\Models\Subscription([
            'gateway' => 'stripe',
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
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = $this->config['webhook_secret'];

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret
            );

            // Handle the event
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    // Handle successful payment
                    break;
                case 'payment_intent.payment_failed':
                    $paymentIntent = $event->data->object;
                    // Handle failed payment
                    break;
                // Add more event types as needed
                default:
                    // Unexpected event type
                    break;
            }

            return true;
        } catch (\Exception $e) {
            // Invalid payload or signature
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
        return 'stripe';
    }
}