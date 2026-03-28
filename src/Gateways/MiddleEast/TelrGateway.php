<?php

namespace ShamimStack\AllInOnePayment\Gateways\MiddleEast;

use ShamimStack\AllInOnePayment\Contracts\PaymentGateway;
use ShamimStack\AllInOnePayment\Contracts\PaymentResponse;
use ShamimStack\AllInOnePayment\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TelrGateway implements PaymentGateway
{
    protected $config;
    protected $merchantId;
    protected $apiKey;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->merchantId = $config['merchant_id'] ?? '';
        $this->apiKey = $config['api_key'] ?? '';
    }

    public function pay(array $data): PaymentResponse
    {
        try {
            if (!isset($data['amount']) || !isset($data['email']) || !isset($data['description'])) {
                throw new PaymentException('Amount, email, and description are required for Telr.');
            }

            $orderId = $data['order_id'] ?? 'ORDER_' . uniqid();

            $payload = [
                'merchant' => [
                    'id' => $this->merchantId,
                ],
                'store' => [
                    'id' => $this->merchantId,
                ],
                'order' => [
                    'id' => $orderId,
                    'description' => $data['description'],
                    'currency' => $data['currency'] ?? 'USD',
                    'amount' => (float)$data['amount'],
                    'billing' => [
                        'name' => $data['name'] ?? '',
                        'email' => $data['email'],
                        'phone' => $data['phone'] ?? '',
                        'address' => [
                            'line1' => $data['address'] ?? '',
                            'city' => $data['city'] ?? '',
                            'region' => $data['state'] ?? '',
                            'country' => $data['country'] ?? '',
                            'zip' => $data['postal_code'] ?? '',
                        ],
                    ],
                ],
                'return' => [
                    'url' => $data['return_url'] ?? $this->config['return_url'] ?? '',
                ],
                'params' => [
                    'authkey' => $this->apiKey,
                ],
            ];

            $headers = [
                'Content-Type' => 'application/json',
            ];

            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($this->getEndpoint() . '/v1/checkout', $payload);

            $result = $response->json();

            if (isset($result['order']) && isset($result['order']['id'])) {
                return new PaymentResponse(
                    true,
                    $result['order']['id'],
                    [
                        'gatewayTransactionId' => $result['order']['id'],
                        'data' => [
                            'order_id' => $result['order']['id'],
                            'status' => $result['order']['status'] ?? null,
                            'redirect_url' => $result['order']['url'] ?? null,
                        ],
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $result['error'] ?? 'Telr payment failed',
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
                'merchant' => [
                    'id' => $this->merchantId,
                ],
                'order' => [
                    'id' => $transactionId,
                ],
                'refund' => [
                    'type' => $amount !== null ? 'partial' : 'full',
                    'amount' => $amount ?? 0,
                ],
            ];

            $headers = [
                'Content-Type' => 'application/json',
            ];

            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($this->getEndpoint() . '/v1/refund', $payload);

            $result = $response->json();

            if (isset($result['order'])) {
                return new PaymentResponse(
                    true,
                    $result['order']['id'],
                    [
                        'gatewayTransactionId' => $result['order']['id'],
                        'data' => $result['order'],
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $result['error'] ?? 'Telr refund failed',
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
                'errorMessage' => 'Telr does not support direct cancellation. Use refund instead.',
                'data' => [
                    'suggestion' => 'Use refund() to reverse the transaction.',
                ],
            ]
        );
    }

    public function subscribe(array $data): \ShamimStack\AllInOnePayment\Models\Subscription
    {
        return new \ShamimStack\AllInOnePayment\Models\Subscription([
            'gateway' => 'telr',
            'gateway_subscription_id' => 'sub_telr_' . uniqid(),
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

            if (isset($payload['order']) && isset($payload['order']['status'])) {
                return in_array($payload['order']['status'], ['paid', 'captured']);
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getName(): string
    {
        return 'telr';
    }

    protected function getEndpoint(): string
    {
        return $this->config['mode'] === 'live'
            ? 'https://secure.inicentral.com'
            : 'https://secure-test.inicentral.com';
    }
}
