<?php

namespace ShamimStack\WwwPay\Gateways\Europe;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class iDEALGateway implements PaymentGateway
{
    protected $config;
    protected $apiKey;
    protected $merchantId;
    protected $subToken;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->apiKey = $config['api_key'] ?? '';
        $this->merchantId = $config['merchant_id'] ?? '';
        $this->subToken = $config['subtoken'] ?? '';
    }

    public function pay(array $data): PaymentResponse
    {
        try {
            if (!isset($data['amount']) || !isset($data['description'])) {
                throw new PaymentException('Amount and description are required for iDEAL payment.');
            }

            $orderId = $data['order_id'] ?? 'ORDER_' . uniqid();
            $amount = (int)round($data['amount'] * 100);
            $currency = 'EUR';

            $issuers = $this->getIssuers();
            $issuerId = $data['issuer_id'] ?? null;

            if (!$issuerId && isset($data['issuer'])) {
                $issuerId = $this->findIssuerId($issuers, $data['issuer']);
            }

            $payload = [
                'amount' => [
                    'value' => $amount,
                    'currency' => $currency,
                ],
                'description' => $data['description'],
                'redirectUrl' => $data['return_url'] ?? $this->config['return_url'] ?? '',
                'metadata' => [
                    'order_id' => $orderId,
                ],
            ];

            if ($issuerId) {
                $payload['issuer'] = $issuerId;
            }

            $headers = [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Idempotency-Key' => $orderId,
            ];

            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($this->getEndpoint() . '/v1/payments', $payload);

            $result = $response->json();

            if (isset($result['id'])) {
                return new PaymentResponse(
                    true,
                    $result['id'],
                    [
                        'gatewayTransactionId' => $result['id'],
                        'data' => [
                            'payment_id' => $result['id'],
                            'status' => $result['status'] ?? 'pending',
                            'redirect_url' => $result['redirectUrl'] ?? null,
                            'expires_at' => $result['expiresAt'] ?? null,
                        ],
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $result['errorMessage'] ?? $result['message'] ?? 'iDEAL payment failed',
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
                'description' => 'Refund for order',
            ];

            if ($amount !== null) {
                $payload['amount'] = [
                    'value' => (int)round($amount * 100),
                    'currency' => 'EUR',
                ];
            }

            $headers = [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Idempotency-Key' => 'REFUND_' . uniqid(),
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
                    'errorMessage' => $result['errorMessage'] ?? $result['message'] ?? 'iDEAL refund failed',
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
                'errorMessage' => 'iDEAL does not support direct cancellation. Wait for payment to expire or use refund.',
                'data' => [
                    'suggestion' => 'iDEAL payments automatically expire after 10 minutes if not completed.',
                ],
            ]
        );
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Models\Subscription
    {
        return new \ShamimStack\WwwPay\Models\Subscription([
            'gateway' => 'ideal',
            'gateway_subscription_id' => 'sub_ideal_' . uniqid(),
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

            if (isset($payload['status']) && $payload['status'] === 'paid') {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getName(): string
    {
        return 'ideal';
    }

    protected function getEndpoint(): string
    {
        return $this->config['mode'] === 'live'
            ? 'https://api.mollie.com'
            : 'https://api.mollie.com';
    }

    protected function getIssuers(): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->get($this->getEndpoint() . '/v1/issuers');

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }

    protected function findIssuerId(array $issuers, string $name): ?string
    {
        foreach ($issuers as $issuer) {
            if (stripos($issuer['name'], $name) !== false) {
                return $issuer['id'];
            }
        }

        return null;
    }
}
