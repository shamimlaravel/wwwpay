<?php

namespace ShamimStack\WwwPay\Gateways\Europe;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AdyenGateway implements PaymentGateway
{
    protected $config;
    protected $apiKey;
    protected $merchantAccount;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->apiKey = $config['api_key'] ?? '';
        $this->merchantAccount = $config['merchant_account'] ?? '';
    }

    public function pay(array $data): PaymentResponse
    {
        try {
            if (!isset($data['amount']) || !isset($data['reference'])) {
                throw new PaymentException('Amount and reference are required for Adyen payment.');
            }

            $reference = $data['reference'];
            $amount = [
                'value' => (int)round($data['amount'] * 100),
                'currency' => $data['currency'] ?? 'EUR',
            ];

            $payload = [
                'amount' => $amount,
                'reference' => $reference,
                'merchantAccount' => $this->merchantAccount,
                'returnUrl' => $data['return_url'] ?? $this->config['return_url'] ?? '',
            ];

            if (isset($data['payment_method'])) {
                $payload['paymentMethod'] = $data['payment_method'];
            }

            if (isset($data['shopper_email'])) {
                $payload['shopperEmail'] = $data['shopper_email'];
            }

            if (isset($data['shopper_reference'])) {
                $payload['shopperReference'] = $data['shopper_reference'];
            }

            $headers = [
                'Authorization' => 'Basic ' . base64_encode($this->merchantAccount . ':' . $this->apiKey),
                'Content-Type' => 'application/json',
                'Idempotency-Key' => Str::uuid()->toString(),
            ];

            $endpoint = $this->getEndpoint();
            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($endpoint . '/v71/payments', $payload);

            $result = $response->json();

            if (isset($result['resultCode'])) {
                $isSuccess = in_array($result['resultCode'], ['Authorised', 'Received', 'Pending']);

                $responseData = [
                    'result_code' => $result['resultCode'],
                    'psp_reference' => $result['pspReference'] ?? null,
                ];

                if (isset($result['action'])) {
                    $responseData['action'] = $result['action'];
                }

                return new PaymentResponse(
                    $isSuccess,
                    $result['pspReference'] ?? $reference,
                    [
                        'gatewayTransactionId' => $result['pspReference'] ?? null,
                        'data' => $responseData,
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $result['message'] ?? 'Adyen payment failed',
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
                'merchantAccount' => $this->merchantAccount,
                'originalReference' => $transactionId,
            ];

            if ($amount !== null) {
                $payload['amount'] = [
                    'value' => (int)round($amount * 100),
                    'currency' => $this->config['currency'] ?? 'EUR',
                ];
            }

            $headers = [
                'Authorization' => 'Basic ' . base64_encode($this->merchantAccount . ':' . $this->apiKey),
                'Content-Type' => 'application/json',
                'Idempotency-Key' => Str::uuid()->toString(),
            ];

            $endpoint = $this->getEndpoint();
            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($endpoint . '/v71/payments/' . $transactionId . '/refunds', $payload);

            $result = $response->json();

            if (isset($result['pspReference'])) {
                return new PaymentResponse(
                    true,
                    $result['pspReference'],
                    [
                        'gatewayTransactionId' => $result['pspReference'],
                        'data' => [
                            'status' => $result['status'] ?? 'received',
                            'psp_reference' => $result['pspReference'],
                        ],
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $result['message'] ?? 'Adyen refund failed',
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

            $payload = [
                'merchantAccount' => $this->merchantAccount,
                'originalReference' => $transactionId,
            ];

            $headers = [
                'Authorization' => 'Basic ' . base64_encode($this->merchantAccount . ':' . $this->apiKey),
                'Content-Type' => 'application/json',
                'Idempotency-Key' => Str::uuid()->toString(),
            ];

            $endpoint = $this->getEndpoint();
            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($endpoint . '/v71/payments/' . $transactionId . '/cancels', $payload);

            $result = $response->json();

            if (isset($result['pspReference'])) {
                return new PaymentResponse(
                    true,
                    $result['pspReference'],
                    [
                        'gatewayTransactionId' => $result['pspReference'],
                        'data' => [
                            'status' => $result['status'] ?? 'received',
                        ],
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $result['message'] ?? 'Adyen cancel failed',
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
            'gateway' => 'adyen',
            'gateway_subscription_id' => 'sub_adyen_' . uniqid(),
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

            if (isset($payload['notificationItems'])) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getName(): string
    {
        return 'adyen';
    }

    protected function getEndpoint(): string
    {
        return $this->config['mode'] === 'live'
            ? 'https://checkout-live.adyen.com'
            : 'https://checkout-test.adyen.com';
    }
}
