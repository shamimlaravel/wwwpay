<?php

namespace ShamimStack\AllInOnePayment\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use ShamimStack\AllInOnePayment\Contracts\PaymentGateway;
use ShamimStack\AllInOnePayment\Models\Transaction;

class WebhookHandler
{
    protected array $handlers = [];
    protected array $signatureVerifier = [];

    public function register(string $gateway, callable $handler): self
    {
        $this->handlers[$gateway] = $handler;
        return $this;
    }

    public function registerSignatureVerifier(string $gateway, callable $verifier): self
    {
        $this->signatureVerifier[$gateway] = $verifier;
        return $this;
    }

    public function handle(string $gateway, Request $request): bool
    {
        $payload = $request->all();
        $headers = $request->headers->all();

        Log::info("Webhook received for gateway: {$gateway}", [
            'gateway' => $gateway,
            'payload_keys' => array_keys($payload),
        ]);

        if (!$this->verifySignature($gateway, $payload, $headers)) {
            Log::warning("Invalid webhook signature for gateway: {$gateway}");
            return false;
        }

        if (!isset($this->handlers[$gateway])) {
            Log::warning("No handler registered for gateway: {$gateway}");
            return false;
        }

        try {
            $result = ($this->handlers[$gateway])($payload, $gateway);
            
            if ($result) {
                $this->processWebhook($gateway, $payload);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("Webhook processing error for gateway: {$gateway}", [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
            return false;
        }
    }

    protected function verifySignature(string $gateway, array $payload, array $headers): bool
    {
        if (!isset($this->signatureVerifier[$gateway])) {
            return true;
        }

        return ($this->signatureVerifier[$gateway])($payload, $headers);
    }

    protected function processWebhook(string $gateway, array $payload): void
    {
        $transactionId = $this->extractTransactionId($gateway, $payload);
        $status = $this->extractStatus($gateway, $payload);

        if (!$transactionId) {
            return;
        }

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

        switch ($status) {
            case 'completed':
            case 'succeeded':
            case 'approved':
            case 'paid':
                $transaction->markAsCompleted();
                break;

            case 'failed':
            case 'declined':
            case 'error':
                $transaction->markAsFailed($payload['error_message'] ?? 'Payment failed');
                break;

            case 'refunded':
                $transaction->processRefund();
                break;

            case 'cancelled':
            case 'voided':
                $transaction->cancel();
                break;

            case 'pending':
            case 'processing':
                $transaction->markAsProcessing();
                break;
        }
    }

    protected function extractTransactionId(string $gateway, array $payload): ?string
    {
        $mappings = [
            'stripe' => $payload['data']['object']['id'] ?? null,
            'paypal' => $payload['resource']['id'] ?? $payload['txn_id'] ?? null,
            'bKash' => $payload['trx_id'] ?? null,
            'nagad' => $payload['payment_ref_id'] ?? null,
            'paystack' => $payload['data']['reference'] ?? null,
            'flutterwave' => $payload['data']['tx_ref'] ?? null,
            'mercadopago' => $payload['data']['id'] ?? null,
            'wechat' => $payload['transaction_id'] ?? null,
            'alipay' => $payload['trade_no'] ?? null,
            'square' => $payload['merchant_id'] ?? null,
            'authorize' => $payload['transId'] ?? null,
            'moneris' => $payload['transaction_id'] ?? null,
            'paytabs' => $payload['reference_id'] ?? null,
            'telr' => $payload['cart_id'] ?? null,
            'mada' => $payload['payment_id'] ?? null,
            'payfast' => $payload['m_payment_id'] ?? null,
            'snapscan' => $payload['merchant_reference'] ?? null,
            'bitcoin' => $payload['tx_hash'] ?? null,
            'ethereum' => $payload['tx_hash'] ?? null,
            'klarna' => $payload['order_id'] ?? null,
            'adyen' => $payload['pspReference'] ?? null,
            'ideal' => $payload['payment_id'] ?? null,
            'bancontact' => $payload['payment_id'] ?? null,
            'paypay' => $payload['payment_id'] ?? null,
            'linepay' => $payload['transaction_id'] ?? null,
            'grabpay' => $payload['txID'] ?? null,
            'sepa' => $payload['payment_request_id'] ?? null,
        ];

        return $mappings[$gateway] ?? $payload['transaction_id'] ?? $payload['reference'] ?? null;
    }

    protected function extractStatus(string $gateway, array $payload): ?string
    {
        $mappings = [
            'stripe' => $payload['data']['object']['status'] ?? null,
            'paypal' => $payload['event_type'] ?? null,
            'paystack' => $payload['data']['status'] ?? null,
            'flutterwave' => $payload['data']['status'] ?? null,
            'mercadopago' => $payload['data']['status'] ?? null,
            'wechat' => $payload['trade_state'] ?? null,
            'alipay' => $payload['trade_status'] ?? null,
        ];

        return $mappings[$gateway] ?? $payload['status'] ?? null;
    }

    public static function stripeVerifier(array $payload, array $headers, string $secret): bool
    {
        $signature = $headers['stripe-signature'][0] ?? '';
        
        if (empty($signature)) {
            return false;
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
            return false;
        }

        $payloadBody = json_encode($payload);
        $expectedSignature = hash_hmac('sha256', "{$timestamp}.{$payloadBody}", $secret);

        foreach ($signatures as $signature) {
            if (hash_equals($expectedSignature, $signature)) {
                return true;
            }
        }

        return false;
    }

    public static function paypalVerifier(array $payload, array $headers, string $webhookId, string $clientId, string $clientSecret): bool
    {
        return true;
    }

    public static function genericVerifier(array $payload, array $headers, string $secret, string $signatureHeader, string $signatureKey = 'signature'): bool
    {
        $providedSignature = $headers[strtolower($signatureHeader)][0] ?? '';
        
        if (empty($providedSignature)) {
            return false;
        }

        $signatureData = $payload;
        unset($signatureData[$signatureKey]);

        ksort($signatureData);
        $signatureString = json_encode($signatureData);
        $expectedSignature = hash_hmac('sha256', $signatureString, $secret);

        return hash_equals($expectedSignature, $providedSignature);
    }
}
