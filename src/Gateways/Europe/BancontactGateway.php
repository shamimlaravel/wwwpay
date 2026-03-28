<?php

namespace ShamimStack\WwwPay\Gateways\Europe;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BancontactGateway implements PaymentGateway
{
    protected $config;
    protected $apiKey;
    protected $merchantId;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->apiKey = $config['api_key'] ?? '';
        $this->merchantId = $config['merchant_id'] ?? '';
    }

    public function pay(array $data): PaymentResponse
    {
        try {
            if (!isset($data['amount']) || !isset($data['description'])) {
                throw new PaymentException('Amount and description are required for Bancontact payment.');
            }

            $orderId = $data['order_id'] ?? 'ORDER_' . uniqid();
            $amount = (int)round($data['amount'] * 100);
            $currency = 'EUR';

            $payload = [
                'order_id' => $orderId,
                'amount' => $amount,
                'currency' => $currency,
                'description' => $data['description'],
                'return_url' => $data['return_url'] ?? $this->config['return_url'] ?? '',
            ];

            if (isset($data['customer_email'])) {
                $payload['customer_email'] = $data['customer_email'];
            }

            $headers = [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ];

            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($this->getEndpoint() . '/v1/payments', $payload);

            $result = $response->json();

            if (isset($result['payment_url']) || isset($result['id'])) {
                return new PaymentResponse(
                    true,
                    $result['id'] ?? $orderId,
                    [
                        'gatewayTransactionId' => $result['id'] ?? null,
                        'data' => [
                            'payment_id' => $result['id'] ?? null,
                            'payment_url' => $result['payment_url'] ?? null,
                            'status' => $result['status'] ?? 'pending',
                        ],
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $result['error_message'] ?? $result['message'] ?? 'Bancontact payment failed',
                    'data' => $result,
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

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        try {
            if (empty($transactionId)) {
                throw new PaymentException('Transaction ID is required for refund.');
            }

            $payload = [
                'refund_id' => 'REFUND_' . uniqid(),
            ];

            if ($amount !== null) {
                $payload['amount'] = (int)round($amount * 100);
            }

            $headers = [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ];

            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($this->getEndpoint() . '/v1/payments/' . $transactionId . '/refunds', $payload);

            $result = $response->json();

            if (isset($result['id'])) {
                return new PaymentResponse(
                    true,
                    $result['id'],
                    [
                        'gatewayTransactionId' => $result['id'],
                        'data' => [
                            'refund_id' => $result['id'],
                            'status' => $result['status'] ?? 'pending',
                        ],
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $result['error_message'] ?? $result['message'] ?? 'Bancontact refund failed',
                    'data' => $result,
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

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(
            false,
            $transactionId,
            [
                'errorMessage' => 'Bancontact does not support direct cancellation. Use refund for completed payments.',
                'data' => [
                    'suggestion' => 'Use refund() to reverse the transaction.',
                ],
            ]
        );
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Models\Subscription
    {
        return new \ShamimStack\WwwPay\Models\Subscription([
            'gateway' => 'bancontact',
            'gateway_subscription_id' => 'sub_bancontact_' . uniqid(),
            'status' => 'active',
            'plan_id' => $data['plan_id'] ?? null,
            'customer_id' => $data['customer_id'] ?? null,
            'start_date' => now(),
            'end_date' => null,
        ]);
    }

    public function handleWebhook(\Illuminate\Http\Request $request): bool
    {
        try {
            $payload = $request->all();

            if (isset($payload['status']) && in_array($payload['status'], ['completed', 'paid'])) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getName(): string
    {
        return 'bancontact';
    }

    protected function getEndpoint(): string
    {
        return $this->config['mode'] === 'live'
            ? 'https://api.bancontact.com'
            : 'https://api.sandbox.bancontact.com';
    }
}
