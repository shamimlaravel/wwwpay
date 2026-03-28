<?php

namespace ShamimStack\AllInOnePayment\Webhooks;

use Illuminate\Http\Request;
use ShamimStack\AllInOnePayment\Models\Transaction;
use Illuminate\Support\Facades\Log;

trait GatewayWebhooks
{
    protected array $webhookEvents = [];

    public function handleWebhook(Request $request): bool
    {
        $payload = $request->all();
        $gateway = $this->getName();

        Log::info("Processing webhook for {$gateway}", ['payload' => $this->sanitizePayload($payload)]);

        $eventType = $this->extractEventType($payload);
        $transactionId = $this->extractTransactionId($payload);

        if (!$eventType) {
            Log::warning("No event type found in webhook for {$gateway}");
            return false;
        }

        $this->processWebhookEvent($gateway, $eventType, $transactionId, $payload);

        return true;
    }

    protected function sanitizePayload(array $payload): array
    {
        $sensitiveFields = ['card_number', 'cvv', 'password', 'secret', 'token'];
        foreach ($sensitiveFields as $field) {
            if (isset($payload[$field])) {
                $payload[$field] = '***REDACTED***';
            }
        }
        return $payload;
    }

    protected function processWebhookEvent(string $gateway, string $eventType, ?string $transactionId, array $payload): void
    {
        $this->webhookEvents[] = [
            'gateway' => $gateway,
            'event_type' => $eventType,
            'transaction_id' => $transactionId,
            'timestamp' => now()->toIso8601String(),
        ];

        if ($transactionId) {
            $this->updateTransaction($gateway, $transactionId, $eventType, $payload);
        }
    }

    protected function updateTransaction(string $gateway, string $transactionId, string $eventType, array $payload): void
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

        $statusMap = $this->getStatusMapping($gateway, $eventType);
        
        if ($statusMap) {
            $transaction->update($statusMap);
            Log::info("Transaction updated via webhook", [
                'gateway' => $gateway,
                'transaction_id' => $transactionId,
                'new_status' => $statusMap['status'] ?? 'unknown',
            ]);
        }
    }

    protected function getStatusMapping(string $gateway, string $eventType): ?array
    {
        $mappings = [
            'stripe' => [
                'payment_intent.succeeded' => ['status' => Transaction::STATUS_COMPLETED],
                'payment_intent.payment_failed' => ['status' => Transaction::STATUS_FAILED],
                'charge.refunded' => ['status' => Transaction::STATUS_REFUNDED],
                'charge.dispute.created' => ['status' => 'disputed'],
            ],
            'paypal' => [
                'PAYMENT.CAPTURE.COMPLETED' => ['status' => Transaction::STATUS_COMPLETED],
                'PAYMENT.CAPTURE.DENIED' => ['status' => Transaction::STATUS_FAILED],
                'PAYMENT.CAPTURE.REFUNDED' => ['status' => Transaction::STATUS_REFUNDED],
            ],
            'paystack' => [
                'charge.success' => ['status' => Transaction::STATUS_COMPLETED],
                'charge.failed' => ['status' => Transaction::STATUS_FAILED],
                'refund.processed' => ['status' => Transaction::STATUS_REFUNDED],
            ],
            'flutterwave' => [
                'charge.completed' => ['status' => Transaction::STATUS_COMPLETED],
                'charge.failed' => ['status' => Transaction::STATUS_FAILED],
                'refund.processed' => ['status' => Transaction::STATUS_REFUNDED],
            ],
            'mercadopago' => [
                'payment' => ['status' => Transaction::STATUS_COMPLETED],
            ],
            'bKash' => [
                'payment_success' => ['status' => Transaction::STATUS_COMPLETED],
                'payment_failed' => ['status' => Transaction::STATUS_FAILED],
            ],
        ];

        return $mappings[$gateway][$eventType] ?? null;
    }

    protected function extractEventType(array $payload): ?string
    {
        return $payload['type'] 
            ?? $payload['event_type'] 
            ?? $payload['event'] 
            ?? $payload['status'] 
            ?? null;
    }

    protected function extractTransactionId(array $payload): ?string
    {
        return $payload['id'] 
            ?? $payload['transaction_id'] 
            ?? $payload['reference'] 
            ?? $payload['tx_ref'] 
            ?? $payload['order_id'] 
            ?? null;
    }

    public function getWebhookEvents(): array
    {
        return $this->webhookEvents;
    }

    public function clearWebhookEvents(): void
    {
        $this->webhookEvents = [];
    }
}
