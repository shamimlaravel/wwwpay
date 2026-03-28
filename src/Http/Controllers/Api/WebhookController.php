<?php

namespace ShamimStack\WwwPay\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use ShamimStack\WwwPay\Facades\Payment;
use ShamimStack\WwwPay\Events\WebhookReceived;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request, string $gateway): JsonResponse
    {
        $payload = $request->all();
        
        try {
            $verified = $this->verifyWebhookSignature($request, $gateway);
            
            $event = $this->parseWebhookEvent($gateway, $payload);
            
            WebhookReceived::dispatch(
                $gateway,
                $event['transaction_id'] ?? '',
                $event['amount'] ?? 0,
                $event['currency'] ?? 'USD',
                $event['type'],
                $payload,
                $verified
            );

            $this->processWebhookEvent($gateway, $event);

            return response()->json([
                'success' => true,
                'event' => $event['type'] ?? 'received',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'gateway' => $gateway,
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    protected function verifyWebhookSignature(Request $request, string $gateway): bool
    {
        $gatewayInstance = Payment::gateway($gateway);
        
        if (method_exists($gatewayInstance, 'verifyWebhook')) {
            return $gatewayInstance->verifyWebhook($request);
        }

        return true;
    }

    protected function parseWebhookEvent(string $gateway, array $payload): array
    {
        return match ($gateway) {
            'stripe' => $this->parseStripeWebhook($payload),
            'paypal' => $this->parsePayPalWebhook($payload),
            'paystack' => $this->parsePaystackWebhook($payload),
            'flutterwave' => $this->parseFlutterwaveWebhook($payload),
            'bkash' => $this->parseBkashWebhook($payload),
            default => $this->parseGenericWebhook($payload),
        };
    }

    protected function parseStripeWebhook(array $payload): array
    {
        $type = $payload['type'] ?? '';
        $data = $payload['data']['object'] ?? [];

        return [
            'type' => $type,
            'transaction_id' => $data['id'] ?? '',
            'amount' => ($data['amount'] ?? 0) / 100,
            'currency' => strtoupper($data['currency'] ?? 'USD'),
            'status' => $this->mapStripeStatus($type),
        ];
    }

    protected function parsePayPalWebhook(array $payload): array
    {
        $type = $payload['event_type'] ?? '';
        $resource = $payload['resource'] ?? [];

        return [
            'type' => $type,
            'transaction_id' => $resource['id'] ?? '',
            'amount' => (float) ($resource['amount']['value'] ?? 0),
            'currency' => $resource['amount']['currency_code'] ?? 'USD',
            'status' => $this->mapPayPalStatus($type),
        ];
    }

    protected function parsePaystackWebhook(array $payload): array
    {
        $event = $payload['event'] ?? '';
        $data = $payload['data'] ?? [];

        return [
            'type' => $event,
            'transaction_id' => $data['reference'] ?? '',
            'amount' => ($data['amount'] ?? 0) / 100,
            'currency' => $data['currency'] ?? 'NGN',
            'status' => $data['status'] ?? 'pending',
        ];
    }

    protected function parseFlutterwaveWebhook(array $payload): array
    {
        $event = $payload['event'] ?? '';
        $data = $payload['data'] ?? [];

        return [
            'type' => $event,
            'transaction_id' => $data['tx_ref'] ?? '',
            'amount' => (float) ($data['amount'] ?? 0),
            'currency' => $data['currency'] ?? 'USD',
            'status' => $data['status'] ?? 'pending',
        ];
    }

    protected function parseBkashWebhook(array $payload): array
    {
        $status = $payload['status'] ?? '';
        
        return [
            'type' => $status,
            'transaction_id' => $payload['trx_id'] ?? '',
            'amount' => (float) ($payload['amount'] ?? 0),
            'currency' => 'BDT',
            'status' => $status === 'Success' ? 'completed' : 'pending',
        ];
    }

    protected function parseGenericWebhook(array $payload): array
    {
        return [
            'type' => $payload['type'] ?? $payload['event'] ?? 'unknown',
            'transaction_id' => $payload['id'] ?? $payload['transaction_id'] ?? '',
            'amount' => (float) ($payload['amount'] ?? 0),
            'currency' => $payload['currency'] ?? 'USD',
            'status' => $payload['status'] ?? 'pending',
        ];
    }

    protected function processWebhookEvent(string $gateway, array $event): void
    {
        $type = $event['type'];
        
        match (true) {
            str_contains($type, 'payment') && str_contains($type, 'success') => $this->handlePaymentSuccess($event),
            str_contains($type, 'payment') && str_contains($type, 'fail') => $this->handlePaymentFailed($event),
            str_contains($type, 'refund') => $this->handleRefund($event),
            str_contains($type, 'subscription') => $this->handleSubscription($event),
            default => null,
        };
    }

    protected function handlePaymentSuccess(array $event): void
    {
        Log::info('Webhook: Payment successful', $event);
    }

    protected function handlePaymentFailed(array $event): void
    {
        Log::warning('Webhook: Payment failed', $event);
    }

    protected function handleRefund(array $event): void
    {
        Log::info('Webhook: Refund processed', $event);
    }

    protected function handleSubscription(array $event): void
    {
        Log::info('Webhook: Subscription event', $event);
    }

    protected function mapStripeStatus(string $type): string
    {
        return match ($type) {
            'payment_intent.succeeded', 'charge.succeeded' => 'completed',
            'payment_intent.payment_failed', 'charge.failed' => 'failed',
            'payment_intent.created' => 'pending',
            default => 'pending',
        };
    }

    protected function mapPayPalStatus(string $type): string
    {
        return match ($type) {
            'PAYMENT.CAPTURE.COMPLETED' => 'completed',
            'PAYMENT.CAPTURE.DENIED' => 'failed',
            'PAYMENT.CAPTURE.PENDING' => 'pending',
            default => 'pending',
        };
    }
}
