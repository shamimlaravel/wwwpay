<?php

namespace ShamimStack\WwwPay\Gateways\LatinAmerica;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;

class MercadoPagoGateway implements PaymentGateway
{
    protected $config;
    protected $accessToken;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->accessToken = $config['access_token'] ?? '';
    }

    public function pay(array $data): PaymentResponse
    {
        try {
            if (!isset($data['amount']) || !isset($data['description'])) {
                throw new PaymentException('Amount and description are required for MercadoPago.');
            }

            $externalReference = $data['order_id'] ?? 'ORDER_' . uniqid();
            $amount = $data['amount'];
            $currency = strtoupper($data['currency'] ?? 'BRL');

            $payload = [
                'transaction_amount' => (float)$amount,
                'description' => $data['description'],
                'payment_method_id' => $data['payment_method_id'] ?? null,
                'payer' => [
                    'email' => $data['payer_email'] ?? $data['email'] ?? '',
                ],
                'external_reference' => $externalReference,
                'installments' => $data['installments'] ?? 1,
            ];

            if (isset($data['token'])) {
                $payload['token'] = $data['token'];
            }

            if (isset($data['identification_type']) && isset($data['identification_number'])) {
                $payload['payer']['identification'] = [
                    'type' => $data['identification_type'],
                    'number' => $data['identification_number'],
                ];
            }

            $headers = [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ];

            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($this->getEndpoint() . '/v1/payments', $payload);

            $result = $response->json();

            if (isset($result['status']) && in_array($result['status'], ['approved', 'pending', 'in_process'])) {
                $isSuccess = $result['status'] === 'approved';

                return new PaymentResponse(
                    $isSuccess,
                    (string)$result['id'],
                    [
                        'gatewayTransactionId' => (string)$result['id'],
                        'data' => [
                            'status' => $result['status'],
                            'status_detail' => $result['status_detail'] ?? null,
                            'payment_method_id' => $result['payment_method_id'] ?? null,
                            'external_reference' => $result['external_reference'] ?? null,
                            'transaction_amount' => $result['transaction_amount'] ?? null,
                        ],
                    ]
                );
            }

            $errorMessage = $result['error'] ?? 'MercadoPago payment failed';
            if (is_array($result) && isset($result['cause'])) {
                $errorMessage = $result['cause'][0]['description'] ?? $errorMessage;
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $errorMessage,
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

            $payload = [];
            if ($amount !== null) {
                $payload['amount'] = $amount;
            }

            $headers = [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ];

            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($this->getEndpoint() . '/v1/payments/' . $transactionId . '/refunds', $payload);

            $result = $response->json();

            if (isset($result['id'])) {
                return new PaymentResponse(
                    true,
                    (string)$result['id'],
                    [
                        'gatewayTransactionId' => (string)$result['id'],
                        'data' => [
                            'payment_id' => $result['payment_id'] ?? null,
                            'amount' => $result['amount'] ?? null,
                            'status' => $result['status'] ?? null,
                        ],
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $result['message'] ?? 'MercadoPago refund failed',
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
        try {
            if (empty($transactionId)) {
                throw new PaymentException('Transaction ID is required.');
            }

            $headers = [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ];

            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->put($this->getEndpoint() . '/v1/payments/' . $transactionId, [
                    'status' => 'cancelled',
                ]);

            $result = $response->json();

            if (isset($result['status']) && $result['status'] === 'cancelled') {
                return new PaymentResponse(
                    true,
                    (string)$result['id'],
                    [
                        'gatewayTransactionId' => (string)$result['id'],
                        'data' => [
                            'status' => $result['status'],
                        ],
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $result['message'] ?? 'MercadoPago cancel failed',
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

    public function subscribe(array $data): \ShamimStack\WwwPay\Models\Subscription
    {
        return new \ShamimStack\WwwPay\Models\Subscription([
            'gateway' => 'mercadopago',
            'gateway_subscription_id' => 'sub_mp_' . uniqid(),
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

            if (isset($payload['action']) && in_array($payload['action'], ['payment.created', 'payment.updated'])) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getName(): string
    {
        return 'mercadopago';
    }

    protected function getEndpoint(): string
    {
        return $this->config['mode'] === 'production'
            ? 'https://api.mercadopago.com'
            : 'https://api.mercadopago.com';
    }
}
