<?php

namespace ShamimStack\AllInOnePayment\Documentation;

use Illuminate\Support\Facades\URL;

class OpenAPIGenerator
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'title' => 'Laravel All-in-One Payment Gateway API',
            'version' => '1.0.0',
            'description' => 'Comprehensive payment gateway API supporting global, regional, and cryptocurrency payment methods',
            'base_url' => null,
        ], $config);
    }

    public function generate(): array
    {
        return [
            'openapi' => '3.0.3',
            'info' => $this->generateInfo(),
            'servers' => $this->generateServers(),
            'paths' => $this->generatePaths(),
            'components' => $this->generateComponents(),
            'tags' => $this->generateTags(),
        ];
    }

    protected function generateInfo(): array
    {
        return [
            'title' => $this->config['title'],
            'description' => $this->config['description'],
            'version' => $this->config['version'],
            'contact' => [
                'name' => 'API Support',
                'email' => 'support@example.com',
            ],
            'license' => [
                'name' => 'MIT',
                'url' => 'https://opensource.org/licenses/MIT',
            ],
        ];
    }

    protected function generateServers(): array
    {
        $baseUrl = $this->config['base_url'] ?? url('/');
        
        return [
            [
                'url' => $baseUrl,
                'description' => 'Current server',
            ],
            [
                'url' => $baseUrl . '/sandbox',
                'description' => 'Sandbox environment',
            ],
        ];
    }

    protected function generatePaths(): array
    {
        return [
            '/api/payment/process' => $this->processPaymentPath(),
            '/api/payment/refund' => $this->refundPath(),
            '/api/payment/cancel' => $this->cancelPath(),
            '/api/payment/webhook/{gateway}' => $this->webhookPath(),
            '/api/payment/transaction/{id}' => $this->transactionPath(),
            '/api/payment/transactions' => $this->transactionsPath(),
            '/api/subscription/create' => $this->subscriptionPath(),
            '/api/subscription/{id}' => $this->subscriptionDetailPath(),
            '/api/reports/summary' => $this->reportsSummaryPath(),
            '/api/reports/gateway-breakdown' => $this->reportsGatewayBreakdownPath(),
        ];
    }

    protected function processPaymentPath(): array
    {
        return [
            'post' => [
                'tags' => ['Payments'],
                'summary' => 'Process a payment',
                'description' => 'Process a payment using any configured gateway',
                'operationId' => 'processPayment',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['gateway', 'amount', 'currency'],
                                'properties' => [
                                    'gateway' => [
                                        'type' => 'string',
                                        'enum' => $this->getGatewayList(),
                                        'example' => 'stripe',
                                    ],
                                    'amount' => [
                                        'type' => 'number',
                                        'format' => 'float',
                                        'example' => 99.99,
                                    ],
                                    'currency' => [
                                        'type' => 'string',
                                        'example' => 'USD',
                                    ],
                                    'payment_method' => [
                                        'type' => 'string',
                                        'description' => 'Payment method identifier (card token, account ID, etc.)',
                                    ],
                                    'customer_id' => [
                                        'type' => 'string',
                                    ],
                                    'customer_email' => [
                                        'type' => 'string',
                                        'format' => 'email',
                                    ],
                                    'description' => [
                                        'type' => 'string',
                                    ],
                                    'metadata' => [
                                        'type' => 'object',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Payment processed successfully',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/PaymentResponse',
                                ],
                            ],
                        ],
                    ],
                    '400' => [
                        'description' => 'Invalid request',
                    ],
                    '500' => [
                        'description' => 'Server error',
                    ],
                ],
            ],
        ];
    }

    protected function refundPath(): array
    {
        return [
            'post' => [
                'tags' => ['Payments'],
                'summary' => 'Refund a payment',
                'operationId' => 'refundPayment',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['gateway', 'transaction_id'],
                                'properties' => [
                                    'gateway' => [
                                        'type' => 'string',
                                        'enum' => $this->getGatewayList(),
                                    ],
                                    'transaction_id' => [
                                        'type' => 'string',
                                    ],
                                    'amount' => [
                                        'type' => 'number',
                                        'description' => 'Amount to refund (optional, full refund if not specified)',
                                    ],
                                    'reason' => [
                                        'type' => 'string',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Refund processed successfully',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/RefundResponse',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function cancelPath(): array
    {
        return [
            'post' => [
                'tags' => ['Payments'],
                'summary' => 'Cancel a payment',
                'operationId' => 'cancelPayment',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['gateway', 'transaction_id'],
                                'properties' => [
                                    'gateway' => [
                                        'type' => 'string',
                                        'enum' => $this->getGatewayList(),
                                    ],
                                    'transaction_id' => [
                                        'type' => 'string',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Cancellation processed',
                    ],
                ],
            ],
        ];
    }

    protected function webhookPath(): array
    {
        return [
            'post' => [
                'tags' => ['Webhooks'],
                'summary' => 'Handle gateway webhook',
                'operationId' => 'handleWebhook',
                'parameters' => [
                    [
                        'name' => 'gateway',
                        'in' => 'path',
                        'required' => true,
                        'schema' => [
                            'type' => 'string',
                            'enum' => $this->getGatewayList(),
                        ],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'description' => 'Webhook payload from the payment gateway',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Webhook processed successfully',
                    ],
                ],
            ],
        ];
    }

    protected function transactionPath(): array
    {
        return [
            'get' => [
                'tags' => ['Transactions'],
                'summary' => 'Get transaction details',
                'operationId' => 'getTransaction',
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'path',
                        'required' => true,
                        'schema' => [
                            'type' => 'integer',
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Transaction details',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/Transaction',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function transactionsPath(): array
    {
        return [
            'get' => [
                'tags' => ['Transactions'],
                'summary' => 'List transactions',
                'operationId' => 'listTransactions',
                'parameters' => [
                    [
                        'name' => 'gateway',
                        'in' => 'query',
                        'schema' => [
                            'type' => 'string',
                        ],
                    ],
                    [
                        'name' => 'status',
                        'in' => 'query',
                        'schema' => [
                            'type' => 'string',
                            'enum' => ['pending', 'completed', 'failed', 'refunded', 'cancelled'],
                        ],
                    ],
                    [
                        'name' => 'start_date',
                        'in' => 'query',
                        'schema' => [
                            'type' => 'string',
                            'format' => 'date',
                        ],
                    ],
                    [
                        'name' => 'end_date',
                        'in' => 'query',
                        'schema' => [
                            'type' => 'string',
                            'format' => 'date',
                        ],
                    ],
                    [
                        'name' => 'page',
                        'in' => 'query',
                        'schema' => [
                            'type' => 'integer',
                            'default' => 1,
                        ],
                    ],
                    [
                        'name' => 'per_page',
                        'in' => 'query',
                        'schema' => [
                            'type' => 'integer',
                            'default' => 20,
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Paginated list of transactions',
                    ],
                ],
            ],
        ];
    }

    protected function subscriptionPath(): array
    {
        return [
            'post' => [
                'tags' => ['Subscriptions'],
                'summary' => 'Create a subscription',
                'operationId' => 'createSubscription',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['gateway', 'plan_id', 'customer_id'],
                                'properties' => [
                                    'gateway' => [
                                        'type' => 'string',
                                        'enum' => $this->getGatewayList(),
                                    ],
                                    'plan_id' => [
                                        'type' => 'string',
                                    ],
                                    'customer_id' => [
                                        'type' => 'string',
                                    ],
                                    'billing_cycle' => [
                                        'type' => 'string',
                                        'enum' => ['daily', 'weekly', 'monthly', 'yearly'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Subscription created',
                    ],
                ],
            ],
        ];
    }

    protected function subscriptionDetailPath(): array
    {
        return [
            'get' => [
                'tags' => ['Subscriptions'],
                'summary' => 'Get subscription details',
                'operationId' => 'getSubscription',
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'path',
                        'required' => true,
                        'schema' => [
                            'type' => 'integer',
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Subscription details',
                    ],
                ],
            ],
            'delete' => [
                'tags' => ['Subscriptions'],
                'summary' => 'Cancel subscription',
                'operationId' => 'cancelSubscription',
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'path',
                        'required' => true,
                        'schema' => [
                            'type' => 'integer',
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Subscription cancelled',
                    ],
                ],
            ],
        ];
    }

    protected function reportsSummaryPath(): array
    {
        return [
            'get' => [
                'tags' => ['Reports'],
                'summary' => 'Get payment summary',
                'operationId' => 'getPaymentSummary',
                'parameters' => [
                    [
                        'name' => 'start_date',
                        'in' => 'query',
                        'schema' => [
                            'type' => 'string',
                            'format' => 'date',
                        ],
                    ],
                    [
                        'name' => 'end_date',
                        'in' => 'query',
                        'schema' => [
                            'type' => 'string',
                            'format' => 'date',
                        ],
                    ],
                    [
                        'name' => 'gateway',
                        'in' => 'query',
                        'schema' => [
                            'type' => 'string',
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Payment summary statistics',
                    ],
                ],
            ],
        ];
    }

    protected function reportsGatewayBreakdownPath(): array
    {
        return [
            'get' => [
                'tags' => ['Reports'],
                'summary' => 'Get gateway breakdown',
                'operationId' => 'getGatewayBreakdown',
                'responses' => [
                    '200' => [
                        'description' => 'Gateway performance breakdown',
                    ],
                ],
            ],
        ];
    }

    protected function generateComponents(): array
    {
        return [
            'schemas' => [
                'PaymentResponse' => [
                    'type' => 'object',
                    'properties' => [
                        'success' => [
                            'type' => 'boolean',
                        ],
                        'transaction_id' => [
                            'type' => 'string',
                        ],
                        'gateway_transaction_id' => [
                            'type' => 'string',
                        ],
                        'error_message' => [
                            'type' => 'string',
                        ],
                        'data' => [
                            'type' => 'object',
                        ],
                    ],
                ],
                'RefundResponse' => [
                    'type' => 'object',
                    'properties' => [
                        'success' => [
                            'type' => 'boolean',
                        ],
                        'refund_id' => [
                            'type' => 'string',
                        ],
                        'amount' => [
                            'type' => 'number',
                        ],
                    ],
                ],
                'Transaction' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => [
                            'type' => 'integer',
                        ],
                        'gateway' => [
                            'type' => 'string',
                        ],
                        'gateway_transaction_id' => [
                            'type' => 'string',
                        ],
                        'order_id' => [
                            'type' => 'string',
                        ],
                        'amount' => [
                            'type' => 'number',
                        ],
                        'currency' => [
                            'type' => 'string',
                        ],
                        'status' => [
                            'type' => 'string',
                        ],
                        'customer_email' => [
                            'type' => 'string',
                        ],
                        'created_at' => [
                            'type' => 'string',
                            'format' => 'date-time',
                        ],
                    ],
                ],
                'Error' => [
                    'type' => 'object',
                    'properties' => [
                        'error' => [
                            'type' => 'string',
                        ],
                        'message' => [
                            'type' => 'string',
                        ],
                        'code' => [
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
            'securitySchemes' => [
                'bearerAuth' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT',
                ],
            ],
        ];
    }

    protected function generateTags(): array
    {
        return [
            [
                'name' => 'Payments',
                'description' => 'Payment processing operations',
            ],
            [
                'name' => 'Webhooks',
                'description' => 'Webhook handling for payment gateways',
            ],
            [
                'name' => 'Transactions',
                'description' => 'Transaction management',
            ],
            [
                'name' => 'Subscriptions',
                'description' => 'Subscription management',
            ],
            [
                'name' => 'Reports',
                'description' => 'Payment analytics and reporting',
            ],
        ];
    }

    protected function getGatewayList(): array
    {
        return [
            'stripe', 'paypal', 'bkash', 'nagad', 'upi', 'phonepe', 'paytm',
            'jazzcash', 'easypaisa', 'paytabs', 'telr', 'mada', 'payfast',
            'snapscan', 'alipay', 'wechat', 'bitcoin', 'ethereum', 'square',
            'authorize', 'moneris', 'mercadopago', 'pagseguro', 'klarna',
            'sepa', 'adyen', 'ideal', 'bancontact', 'paypay', 'linepay',
            'grabpay', 'flutterwave', 'paystack'
        ];
    }

    public function toJSON(): string
    {
        return json_encode($this->generate(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function saveToFile(string $path): bool
    {
        return file_put_contents($path, $this->toJSON()) !== false;
    }
}
