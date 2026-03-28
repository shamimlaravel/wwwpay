<?php

namespace ShamimStack\WwwPay;

use Illuminate\Support\Collection;
use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Contracts\Subscription;
use ShamimStack\WwwPay\Exceptions\InvalidConfigurationException;
use ShamimStack\WwwPay\Exceptions\PaymentException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PaymentManager
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * Create a new payment manager instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get a gateway instance by name.
     *
     * @param  string  $gateway
     * @return \ShamimStack\WwwPay\Contracts\PaymentGateway
     *
     * @throws \ShamimStack\WwwPay\Exceptions\InvalidConfigurationException
     */
    public function gateway(string $gateway): PaymentGateway
    {
        $gateway = strtolower($gateway);

        if (! $this->app->bound("payment.{$gateway}")) {
            throw new InvalidConfigurationException("Payment gateway [{$gateway}] is not configured.");
        }

        return $this->app->make("payment.{$gateway}");
    }

    /**
     * Process a payment with automatic logging and error handling
     *
     * @param string $gateway Gateway name
     * @param array $data Payment data
     * @return PaymentResponse
     */
    public function processPayment(string $gateway, array $data): PaymentResponse
    {
        try {
            // Log payment attempt (without sensitive data)
            $safeData = $this->sanitizeLogData($data);
            Log::info("Processing payment via {$gateway}", $safeData);
            
            // Process the payment
            $response = $this->gateway($gateway)->pay($data);
            
            // Log result
            if ($response->isSuccessful()) {
                Log::info("Payment successful via {$gateway}", [
                    'transaction_id' => $response->getTransactionId(),
                    'gateway_transaction_id' => $response->getGatewayTransactionId(),
                    'amount' => $data['amount'] ?? 0,
                    'currency' => $data['currency'] ?? 'USD'
                ]);
                
                // Store transaction record
                $this->storeTransaction($gateway, $data, $response);
            } else {
                Log::warning("Payment failed via {$gateway}", array_merge($safeData, [
                    'error' => $response->getErrorMessage()
                ]));
            }
            
            return $response;
        } catch (\Exception $e) {
            Log::error("Payment processing exception via {$gateway}", [
                'exception' => $e->getMessage(),
                'data' => $this->sanitizeLogData($data)
            ]);
            
            throw new PaymentException("Payment processing failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Refund a payment with automatic logging
     *
     * @param string $gateway Gateway name
     * @param string $transactionId Transaction ID to refund
     * @param float|null $amount Amount to refund (null for full refund)
     * @return PaymentResponse
     */
    public function processRefund(string $gateway, string $transactionId, float $amount = null): PaymentResponse
    {
        try {
            Log::info("Processing refund via {$gateway}", [
                'transaction_id' => $transactionId,
                'amount' => $amount
            ]);
            
            $response = $this->gateway($gateway)->refund($transactionId, $amount);
            
            if ($response->isSuccessful()) {
                Log::info("Refund successful via {$gateway}", [
                    'transaction_id' => $transactionId,
                    'refund_id' => $response->getTransactionId(),
                    'amount' => $amount
                ]);
                
                // Store refund record
                $this->storeRefund($gateway, $transactionId, $amount, $response);
            } else {
                Log::warning("Refund failed via {$gateway}", [
                    'transaction_id' => $transactionId,
                    'error' => $response->getErrorMessage()
                ]);
            }
            
            return $response;
        } catch (\Exception $e) {
            Log::error("Refund processing exception via {$gateway}", [
                'exception' => $e->getMessage(),
                'transaction_id' => $transactionId,
                'amount' => $amount
            ]);
            
            throw new PaymentException("Refund processing failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Cancel a payment with automatic logging
     *
     * @param string $gateway Gateway name
     * @param string $transactionId Transaction ID to cancel
     * @return PaymentResponse
     */
    public function processCancel(string $gateway, string $transactionId): PaymentResponse
    {
        try {
            Log::info("Processing cancellation via {$gateway}", [
                'transaction_id' => $transactionId
            ]);
            
            $response = $this->gateway($gateway)->cancel($transactionId);
            
            if ($response->isSuccessful()) {
                Log::info("Cancellation successful via {$gateway}", [
                    'transaction_id' => $transactionId,
                    'cancellation_id' => $response->getTransactionId()
                ]);
                
                // Update transaction status
                $this->updateTransactionStatus($gateway, $transactionId, 'cancelled');
            } else {
                Log::warning("Cancellation failed via {$gateway}", [
                    'transaction_id' => $transactionId,
                    'error' => $response->getErrorMessage()
                ]);
            }
            
            return $response;
        } catch (\Exception $e) {
            Log::error("Cancellation processing exception via {$gateway}", [
                'exception' => $e->getMessage(),
                'transaction_id' => $transactionId
            ]);
            
            throw new PaymentException("Cancellation processing failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Create a subscription with automatic logging
     *
     * @param string $gateway Gateway name
     * @param array $data Subscription data
     * @return Subscription
     */
    public function createSubscription(string $gateway, array $data): Subscription
    {
        try {
            Log::info("Creating subscription via {$gateway}", [
                'plan_id' => $data['plan_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null
            ]);
            
            $subscription = $this->gateway($gateway)->subscribe($data);
            
            Log::info("Subscription created via {$gateway}", [
                'subscription_id' => $subscription->getGatewaySubscriptionId(),
                'plan_id' => $data['plan_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null
            ]);
            
            // Store subscription record
            $this->storeSubscription($gateway, $data, $subscription);
            
            return $subscription;
        } catch (\Exception $e) {
            Log::error("Subscription creation exception via {$gateway}", [
                'exception' => $e->getMessage(),
                'plan_id' => $data['plan_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null
            ]);
            
            throw new PaymentException("Subscription creation failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Handle webhook with automatic logging and validation
     *
     * @param string $gateway Gateway name
     * @param \Illuminate\Http\Request $request HTTP request
     * @return bool Success status
     */
    public function handleWebhook(string $gateway, \Illuminate\Http\Request $request): bool
    {
        try {
            // Log webhook receipt (without sensitive data)
            $safeHeaders = $this->sanitizeHeaders($request->headers->all());
            Log::info("Received webhook via {$gateway}", [
                'headers' => $safeHeaders,
                'method' => $request->method(),
                'path' => $request->path()
            ]);
            
            // Verify webhook if the gateway supports it
            $gatewayInstance = $this->gateway($gateway);
            if (method_exists($gatewayInstance, 'handleWebhook')) {
                $result = $gatewayInstance->handleWebhook($request);
                
                Log::info("Webhook processed via {$gateway}", [
                    'success' => $result,
                    'gateway' => $gateway
                ]);
                
                return $result;
            }
            
            // If gateway doesn't have webhook handling, log and return true
            Log::warning("Gateway {$gateway} does not implement webhook handling");
            return true;
        } catch (\Exception $e) {
            Log::error("Webhook handling exception via {$gateway}", [
                'exception' => $e->getMessage(),
                'gateway' => $gateway
            ]);
            
            return false;
        }
    }

    /**
     * Get transaction statistics
     *
     * @return array
     */
    public function getTransactionStats(): array
    {
        // In a real implementation, this would query the database
        // For now, return mock data
        return [
            'total_transactions' => 0,
            'successful_transactions' => 0,
            'failed_transactions' => 0,
            'total_refunds' => 0,
            'total_volume' => 0.00,
            'currency_breakdown' => [],
            'gateway_breakdown' => []
        ];
    }

    /**
     * Sanitize data for logging (remove sensitive information)
     *
     * @param array $data
     * @return array
     */
    protected function sanitizeLogData(array $data): array
    {
        $sensitiveKeys = [
            'card_number', 'cvv', 'expiry_month', 'expiry_year',
            'password', 'secret_key', 'api_key', 'token',
            'bank_account', 'routing_number', 'ssn'
        ];
        
        $sanitized = $data;
        
        foreach ($sensitiveKeys as $key) {
            if (isset($sanitized[$key])) {
                $sanitized[$key] = '***REDACTED***';
            }
        }
        
        // Also sanitize nested arrays
        foreach ($sanitized as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeLogData($value);
            }
        }
        
        return $sanitized;
    }

    /**
     * Sanitize headers for logging
     *
     * @param array $headers
     * @return array
     */
    protected function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = [
            'authorization',
            'x-api-key',
            'x-auth-token',
            'cookie'
        ];
        
        $sanitized = $headers;
        
        foreach ($sensitiveHeaders as $header) {
            $header = strtolower($header);
            if (isset($sanitized[$header])) {
                $sanitized[$header] = '***REDACTED***';
            }
        }
        
        return $sanitized;
    }

    /**
     * Store transaction record (placeholder for database storage)
     *
     * @param string $gateway
     * @param array $data
     * @param PaymentResponse $response
     * @return void
     */
    protected function storeTransaction(string $gateway, array $data, PaymentResponse $response): void
    {
        // In a real implementation, this would store to database
        // For now, we'll just log that we would store it
        Log::debug("Would store transaction record", [
            'gateway' => $gateway,
            'transaction_id' => $response->getTransactionId(),
            'gateway_transaction_id' => $response->getGatewayTransactionId(),
            'amount' => $data['amount'] ?? 0,
            'currency' => $data['currency'] ?? 'USD',
            'status' => $response->isSuccessful() ? 'success' : 'failed'
        ]);
    }

    /**
     * Store refund record (placeholder for database storage)
     *
     * @param string $gateway
     * @param string $transactionId
     * @param float|null $amount
     * @param PaymentResponse $response
     * @return void
     */
    protected function storeRefund(string $gateway, string $transactionId, ?float $amount, PaymentResponse $response): void
    {
        // In a real implementation, this would store to database
        Log::debug("Would store refund record", [
            'gateway' => $gateway,
            'original_transaction_id' => $transactionId,
            'refund_id' => $response->getTransactionId(),
            'amount' => $amount,
            'status' => $response->isSuccessful() ? 'success' : 'failed'
        ]);
    }

    /**
     * Store subscription record (placeholder for database storage)
     *
     * @param string $gateway
     * @param array $data
     * @param Subscription $subscription
     * @return void
     */
    protected function storeSubscription(string $gateway, array $data, Subscription $subscription): void
    {
        // In a real implementation, this would store to database
        Log::debug("Would store subscription record", [
            'gateway' => $gateway,
            'subscription_id' => $subscription->getGatewaySubscriptionId(),
            'plan_id' => $data['plan_id'] ?? null,
            'customer_id' => $data['customer_id'] ?? null,
            'status' => 'active'
        ]);
    }

    /**
     * Update transaction status (placeholder for database update)
     *
     * @param string $gateway
     * @param string $transactionId
     * @param string $status
     * @return void
     */
    protected function updateTransactionStatus(string $gateway, string $transactionId, string $status): void
    {
        // In a real implementation, this would update database
        Log::debug("Would update transaction status", [
            'gateway' => $gateway,
            'transaction_id' => $transactionId,
            'status' => $status
        ]);
    }

    /**
     * Dynamically call the gateway method.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        // Handle dynamic gateway calls like $payment->stripe()->pay($data)
        if (Str::endsWith($method, 'Gateway')) {
            $gatewayName = Str::lower(Str::replace('Gateway', '', $method));
            return $this->gateway($gatewayName);
        }
        
        // Fall back to default gateway
        return $this->gateway(config('payment.default_gateway'))->{$method}(...$parameters);
    }
}