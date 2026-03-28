<?php

namespace ShamimStack\AllInOnePayment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ShamimStack\AllInOnePayment\PaymentManager;
use ShamimStack\AllInOnePayment\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class WebhookController extends Controller
{
    protected PaymentManager $paymentManager;

    public function __construct(PaymentManager $paymentManager)
    {
        $this->paymentManager = $paymentManager;
    }

    public function handle(Request $request, string $gateway)
    {
        $payload = $request->all();
        $headers = $request->headers->all();

        Log::info("Webhook received for {$gateway}", [
            'gateway' => $gateway,
            'ip' => $request->ip(),
            'event' => $this->extractEventType($gateway, $payload),
        ]);

        try {
            $this->verifyWebhookSignature($gateway, $payload, $headers);
            
            $eventType = $this->extractEventType($gateway, $payload);
            $transactionId = $this->extractTransactionId($gateway, $payload);
            
            if ($transactionId) {
                $this->processWebhookEvent($gateway, $eventType, $transactionId, $payload);
            }

            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            Log::error("Webhook processing failed for {$gateway}", [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    protected function verifyWebhookSignature(string $gateway, array $payload, array $headers): void
    {
        switch ($gateway) {
            case 'stripe':
                $this->verifyStripeSignature($payload, $headers);
                break;
            case 'paypal':
                $this->verifyPayPalSignature($payload, $headers);
                break;
            case 'paystack':
                $this->verifyPaystackSignature($payload, $headers);
                break;
            case 'flutterwave':
                $this->verifyFlutterwaveSignature($payload, $headers);
                break;
            default:
                break;
        }
    }

    protected function verifyStripeSignature(array $payload, array $headers): void
    {
        $signature = $headers['stripe-signature'][0] ?? '';
        $webhookSecret = config("payment.gateways.stripe.webhook_secret");

        if (empty($signature) || empty($webhookSecret)) {
            throw new \Exception('Missing Stripe webhook signature');
        }

        $elements = explode(',', $signature);
        $timestamp = null;
        $signatures = [];

        foreach ($elements as $element) {
            $parts = explode('=', $element, 2);
            if (count($parts) === 2) {
                if ($parts[0] === 't') {
                    $timestamp = $parts[1];
                } elseif ($parts[0] === 'v1') {
                    $signatures[] = $parts[1];
                }
            }
        }

        if (!$timestamp || empty($signatures)) {
            throw new \Exception('Invalid Stripe signature format');
        }

        $payloadBody = json_encode($payload);
        $expectedSignature = hash_hmac('sha256', "{$timestamp}.{$payloadBody}", $webhookSecret);

        foreach ($signatures as $signature) {
            if (hash_equals($expectedSignature, $signature)) {
                return;
            }
        }

        throw new \Exception('Invalid Stripe webhook signature');
    }

    protected function verifyPayPalSignature(array $payload, array $headers): void
    {
        return;
    }

    protected function verifyPaystackSignature(array $payload, array $headers): void
    {
        $signature = $headers['x-paystack-signature'][0] ?? '';
        $secretKey = config("payment.gateways.paystack.secret_key");

        if (empty($signature)) {
            throw new \Exception('Missing Paystack webhook signature');
        }

        $expectedSignature = hash_hmac('sha512', json_encode($payload), $secretKey);

        if (!hash_equals($expectedSignature, $signature)) {
            throw new \Exception('Invalid Paystack webhook signature');
        }
    }

    protected function verifyFlutterwaveSignature(array $payload, array $headers): void
    {
        $signature = $headers['verif-hash'][0] ?? '';
        $secretHash = config("payment.gateways.flutterwave.webhook_secret");

        if (empty($signature) || empty($secretHash)) {
            throw new \Exception('Missing Flutterwave webhook signature');
        }

        if (!hash_equals($signature, $secretHash)) {
            throw new \Exception('Invalid Flutterwave webhook signature');
        }
    }

    protected function extractEventType(string $gateway, array $payload): ?string
    {
        return match ($gateway) {
            'stripe' => $payload['type'] ?? null,
            'paypal' => $payload['event_type'] ?? null,
            'paystack' => $payload['event'] ?? null,
            'flutterwave' => $payload['event'] ?? null,
            'mercadopago' => $payload['action'] ?? null,
            'bkash' => $payload['status'] ?? null,
            'alipay' => $payload['trade_status'] ?? null,
            'wechat' => $payload['trade_state'] ?? null,
            'square' => $payload['event_type'] ?? null,
            'authorize' => $payload['eventType'] ?? null,
            default => $payload['status'] ?? $payload['event'] ?? null,
        };
    }

    protected function extractTransactionId(string $gateway, array $payload): ?string
    {
        return match ($gateway) {
            'stripe' => $payload['data']['object']['id'] ?? null,
            'paypal' => $payload['resource']['id'] ?? null,
            'paystack' => $payload['data']['reference'] ?? null,
            'flutterwave' => $payload['data']['tx_ref'] ?? $payload['data']['id'] ?? null,
            'mercadopago' => $payload['data']['id'] ?? null,
            'bkash' => $payload['trx_id'] ?? null,
            'alipay' => $payload['trade_no'] ?? null,
            'wechat' => $payload['transaction_id'] ?? null,
            'square' => $payload['entity']['id'] ?? null,
            'authorize' => $payload['payload']['TransactionResponse']['transId'] ?? null,
            default => $payload['id'] ?? $payload['transaction_id'] ?? $payload['reference'] ?? null,
        };
    }

    protected function processWebhookEvent(string $gateway, ?string $eventType, ?string $transactionId, array $payload): void
    {
        $transaction = Transaction::where('gateway_transaction_id', $transactionId)
            ->orWhere('order_id', $transactionId)
            ->first();

        if (!$transaction) {
            Log::info("Transaction not found for webhook", [
                'gateway' => $gateway,
                'transaction_id' => $transactionId,
            ]);
            return;
        }

        $status = $this->mapEventToStatus($gateway, $eventType, $payload);

        if ($status) {
            $transaction->update(['status' => $status]);
            
            if ($status === Transaction::STATUS_COMPLETED) {
                $transaction->update(['completed_at' => now()]);
            }

            Log::info("Transaction status updated via webhook", [
                'gateway' => $gateway,
                'transaction_id' => $transactionId,
                'new_status' => $status,
            ]);
        }
    }

    protected function mapEventToStatus(string $gateway, ?string $eventType, array $payload): ?string
    {
        $statusMap = [
            'stripe' => [
                'payment_intent.succeeded' => Transaction::STATUS_COMPLETED,
                'payment_intent.payment_failed' => Transaction::STATUS_FAILED,
                'charge.refunded' => Transaction::STATUS_REFUNDED,
                'charge.dispute.created' => 'disputed',
            ],
            'paypal' => [
                'PAYMENT.CAPTURE.COMPLETED' => Transaction::STATUS_COMPLETED,
                'PAYMENT.CAPTURE.DENIED' => Transaction::STATUS_FAILED,
                'PAYMENT.CAPTURE.REFUNDED' => Transaction::STATUS_REFUNDED,
            ],
            'paystack' => [
                'charge.success' => Transaction::STATUS_COMPLETED,
                'charge.failed' => Transaction::STATUS_FAILED,
                'refund.processed' => Transaction::STATUS_REFUNDED,
            ],
            'flutterwave' => [
                'charge.completed' => Transaction::STATUS_COMPLETED,
                'charge.failed' => Transaction::STATUS_FAILED,
                'refund.processed' => Transaction::STATUS_REFUNDED,
            ],
            'mercadopago' => [
                'payment' => Transaction::STATUS_COMPLETED,
            ],
            'bkash' => [
                'payment_success' => Transaction::STATUS_COMPLETED,
                'payment_failed' => Transaction::STATUS_FAILED,
            ],
            'alipay' => [
                'TRADE_FINISHED' => Transaction::STATUS_COMPLETED,
                'TRADE_CLOSED' => Transaction::STATUS_CANCELLED,
            ],
            'wechat' => [
                'SUCCESS' => Transaction::STATUS_COMPLETED,
                'REFUND' => Transaction::STATUS_REFUNDED,
            ],
        ];

        return $statusMap[$gateway][$eventType] ?? null;
    }
}
