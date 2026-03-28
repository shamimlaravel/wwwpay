<?php

namespace ShamimStack\AllInOnePayment\Gateways\MiddleEast;

use ShamimStack\AllInOnePayment\Contracts\PaymentGateway;
use ShamimStack\AllInOnePayment\Contracts\PaymentResponse;
use ShamimStack\AllInOnePayment\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PayTabsGateway implements PaymentGateway
{
    protected $config;
    protected $profileId;
    protected $serverKey;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->profileId = $config['profile_id'] ?? '';
        $this->serverKey = $config['server_key'] ?? '';
    }

    public function pay(array $data): PaymentResponse
    {
        try {
            if (!isset($data['amount']) || !isset($data['email']) || !isset($data['name'])) {
                throw new PaymentException('Amount, email, and name are required for PayTabs.');
            }

            $orderId = $data['order_id'] ?? 'ORDER_' . uniqid();

            $payload = [
                'profile_id' => $this->profileId,
                'tran_type' => 'sale',
                'tran_class' => 'ecom',
                'cart_id' => $orderId,
                'cart_currency' => $data['currency'] ?? 'USD',
                'cart_amount' => (float)$data['amount'],
                'cart_description' => $data['description'] ?? 'Payment',
                'customer_details' => [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? '',
                    'street1' => $data['address'] ?? '',
                    'city' => $data['city'] ?? '',
                    'state' => $data['state'] ?? '',
                    'country' => $data['country'] ?? '',
                    'zip' => $data['postal_code'] ?? '',
                ],
                'return_url' => $data['return_url'] ?? $this->config['return_url'] ?? '',
            ];

            $headers = [
                'Authorization' => $this->serverKey,
                'Content-Type' => 'application/json',
            ];

            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($this->getEndpoint() . '/payment/request', $payload);

            $result = $response->json();

            if (isset($result['tran_ref'])) {
                return new PaymentResponse(
                    true,
                    $result['tran_ref'],
                    [
                        'gatewayTransactionId' => $result['tran_ref'],
                        'data' => [
                            'tran_ref' => $result['tran_ref'],
                            'redirect_url' => $result['redirect_url'] ?? null,
                            'payment_url' => $result['payment_url'] ?? null,
                        ],
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $result['error'] ?? 'PayTabs payment failed',
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
                'profile_id' => $this->profileId,
                'tran_type' => 'refund',
                'tran_class' => 'ecom',
                'cart_id' => 'REFUND_' . uniqid(),
                'cart_currency' => $this->config['currency'] ?? 'USD',
                'cart_amount' => $amount ?? 0,
                'tran_ref' => $transactionId,
            ];

            $headers = [
                'Authorization' => $this->serverKey,
                'Content-Type' => 'application/json',
            ];

            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($this->getEndpoint() . '/payment/query', $payload);

            $result = $response->json();

            if (isset($result['tran_ref'])) {
                return new PaymentResponse(
                    true,
                    $result['tran_ref'],
                    [
                        'gatewayTransactionId' => $result['tran_ref'],
                        'data' => $result,
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $result['error'] ?? 'PayTabs refund failed',
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
                'errorMessage' => 'PayTabs does not support direct cancellation.',
                'data' => [
                    'suggestion' => 'Contact PayTabs support or use refund.',
                ],
            ]
        );
    }

    public function subscribe(array $data): \ShamimStack\AllInOnePayment\Models\Subscription
    {
        return new \ShamimStack\AllInOnePayment\Models\Subscription([
            'gateway' => 'paytabs',
            'gateway_subscription_id' => 'sub_paytabs_' . uniqid(),
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

            if (isset($payload['tran_ref']) && isset($payload['resp_status'])) {
                return $payload['resp_status'] === 'A';
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getName(): string
    {
        return 'paytabs';
    }

    protected function getEndpoint(): string
    {
        return $this->config['mode'] === 'live'
            ? 'https://secure.paytabs.com'
            : 'https://secure-sdk.paytabs.com';
    }
}
