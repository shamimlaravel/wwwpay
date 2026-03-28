<?php

namespace ShamimStack\WwwPay\Gateways\NorthAmerica;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MonerisGateway implements PaymentGateway
{
    protected $config;
    protected $storeId;
    protected $apiToken;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->storeId = $config['store_id'] ?? '';
        $this->apiToken = $config['api_token'] ?? '';
    }

    public function pay(array $data): PaymentResponse
    {
        try {
            if (!isset($data['amount']) || !isset($data['card_number'])) {
                throw new PaymentException('Amount and card_number are required for Moneris payment.');
            }

            $orderId = $data['order_id'] ?? 'ORDER_' . uniqid();
            $amount = $data['amount'];
            $currency = strtoupper($data['currency'] ?? 'CAD');

            $payload = [
                'ps_store_id' => $this->storeId,
                'api_token' => $this->apiToken,
                'order_id' => $orderId,
                'amount' => number_format($amount, 2, '.', ''),
                'pan' => $data['card_number'],
                'expdate' => $this->formatExpiryDate($data['expiry_month'], $data['expiry_year']),
                'crypt_type' => $data['crypt_type'] ?? 7,
            ];

            if (isset($data['cvv'])) {
                $payload['cvd_value'] = $data['cvv'];
            }

            if (isset($data['street_address'])) {
                $payload['billing_address'] = [
                    'street' => $data['street_address'],
                    'city' => $data['city'] ?? '',
                    'province' => $data['province'] ?? '',
                    'postal_code' => $data['postal_code'] ?? '',
                    'country' => $data['country'] ?? 'CA',
                ];
            }

            if (isset($data['customer_id'])) {
                $payload['customer_id'] = $data['customer_id'];
            }

            $response = Http::timeout(30)
                ->asForm()
                ->post($this->getEndpoint() . '/v1/purchase', $payload);

            $result = $response->json();

            if (isset($result['response_code']) && $result['response_code'] === '0') {
                return new PaymentResponse(
                    true,
                    $result['transaction_id'],
                    [
                        'gatewayTransactionId' => $result['transaction_id'],
                        'data' => [
                            'authorization_code' => $result['auth_code'] ?? null,
                            'reference_number' => $result['reference_number'] ?? null,
                            'iso_code' => $result['iso_code'] ?? null,
                        ],
                    ]
                );
            }

            return new PaymentResponse(
                false,
                $result['transaction_id'] ?? null,
                [
                    'errorMessage' => $result['message'] ?? 'Moneris payment failed',
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
                'ps_store_id' => $this->storeId,
                'api_token' => $this->apiToken,
                'order_id' => 'REFUND_' . uniqid(),
                'transaction_id' => $transactionId,
            ];

            if ($amount !== null) {
                $payload['amount'] = number_format($amount, 2, '.', '');
            }

            $response = Http::timeout(30)
                ->asForm()
                ->post($this->getEndpoint() . '/v1/refund', $payload);

            $result = $response->json();

            if (isset($result['response_code']) && $result['response_code'] === '0') {
                return new PaymentResponse(
                    true,
                    $result['transaction_id'],
                    [
                        'gatewayTransactionId' => $result['transaction_id'],
                        'data' => [
                            'refund_amount' => $amount,
                            'approved' => $result['approved'] ?? 'true',
                        ],
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $result['message'] ?? 'Moneris refund failed',
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
                'errorMessage' => 'Moneris does not support void via this API. Use refund for completed transactions.',
                'data' => [
                    'suggestion' => 'Use refund() to reverse the transaction.',
                ],
            ]
        );
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Models\Subscription
    {
        return new \ShamimStack\WwwPay\Models\Subscription([
            'gateway' => 'moneris',
            'gateway_subscription_id' => 'sub_moneris_' . uniqid(),
            'status' => 'active',
            'plan_id' => $data['plan_id'] ?? null,
            'customer_id' => $data['customer_id'] ?? null,
            'start_date' => now(),
            'end_date' => null,
        ]);
    }

    public function handleWebhook(\Illuminate\Http\Request $request): bool
    {
        return true;
    }

    public function getName(): string
    {
        return 'moneris';
    }

    protected function getEndpoint(): string
    {
        return $this->config['mode'] === 'live'
            ? 'https://www.moneris.com'
            : 'https://esqa.moneris.com';
    }

    protected function formatExpiryDate(string $month, string $year): string
    {
        return str_pad($month, 2, '0', STR_PAD_LEFT) . substr($year, -2);
    }
}
