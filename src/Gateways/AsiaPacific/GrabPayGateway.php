<?php

namespace ShamimStack\AllInOnePayment\Gateways\AsiaPacific;

use ShamimStack\AllInOnePayment\Contracts\PaymentGateway;
use ShamimStack\AllInOnePayment\Contracts\PaymentResponse;
use ShamimStack\AllInOnePayment\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GrabPayGateway implements PaymentGateway
{
    protected $config;
    protected $partnerId;
    protected $merchantId;
    protected $clientId;
    protected $clientSecret;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->partnerId = $config['partner_id'] ?? '';
        $this->merchantId = $config['merchant_id'] ?? '';
        $this->clientId = $config['client_id'] ?? $config['clientId'] ?? '';
        $this->clientSecret = $config['client_secret'] ?? $config['clientSecret'] ?? '';
    }

    public function pay(array $data): PaymentResponse
    {
        try {
            if (!isset($data['amount']) || !isset($data['currency'])) {
                throw new PaymentException('Amount and currency are required for Grab Pay.');
            }

            $orderId = $data['order_id'] ?? 'ORDER_' . uniqid();
            $amount = $data['amount'];
            $currency = strtoupper($data['currency']);

            $paymentData = [
                'partnerGroupTxID' => $orderId,
                'partnerTxID' => 'TX_' . uniqid(),
                'amount' => [
                    'currency' => $currency,
                    'value' => (int)round($amount * 100),
                ],
                'merchantExtras' => [
                    'notes' => $data['description'] ?? '',
                ],
            ];

            if (isset($data['return_url'])) {
                $paymentData['redirectUrl'] = [
                    'web' => $data['return_url'],
                    'mobile' => $data['return_url'] ?? '',
                ];
            }

            $authToken = $this->getAccessToken();

            $headers = [
                'Authorization' => 'Bearer ' . $authToken,
                'Content-Type' => 'application/json',
                'X-Availability' => 'MY',
                'X-Platform' => 'mobile',
                'X-Partner-ID' => $this->partnerId,
                'X-Merchant-ID' => $this->merchantId,
            ];

            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($this->getEndpoint() . '/v1/payment', $paymentData);

            $result = $response->json();

            if (isset($result['txID'])) {
                return new PaymentResponse(
                    true,
                    $result['txID'],
                    [
                        'gatewayTransactionId' => $result['txID'],
                        'data' => [
                            'tx_id' => $result['txID'] ?? null,
                            'status' => $result['status'] ?? 'PENDING',
                            'payment_id' => $result['paymentID'] ?? null,
                            'redirect_url' => $result['redirectUrl'] ?? null,
                        ],
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $result['errorDescription'] ?? $result['message'] ?? 'Grab Pay request failed',
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

            $authToken = $this->getAccessToken();

            $refundData = [
                'txID' => $transactionId,
                'reason' => 'Customer request',
            ];

            if ($amount !== null) {
                $refundData['amount'] = [
                    'currency' => $this->config['currency'] ?? 'MYR',
                    'value' => (int)round($amount * 100),
                ];
            }

            $headers = [
                'Authorization' => 'Bearer ' . $authToken,
                'Content-Type' => 'application/json',
                'X-Partner-ID' => $this->partnerId,
                'X-Merchant-ID' => $this->merchantId,
            ];

            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($this->getEndpoint() . '/v1/refund', $refundData);

            $result = $response->json();

            if (isset($result['txID'])) {
                return new PaymentResponse(
                    true,
                    $result['txID'],
                    [
                        'gatewayTransactionId' => $result['txID'],
                        'data' => [
                            'tx_id' => $result['txID'] ?? null,
                            'status' => $result['status'] ?? 'SUCCESS',
                        ],
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $result['errorDescription'] ?? $result['message'] ?? 'Grab Pay refund failed',
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
        return $this->refund($transactionId);
    }

    public function subscribe(array $data): \ShamimStack\AllInOnePayment\Models\Subscription
    {
        return new \ShamimStack\AllInOnePayment\Models\Subscription([
            'gateway' => 'grabpay',
            'gateway_subscription_id' => 'sub_grabpay_' . uniqid(),
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

            if (isset($payload['status']) && in_array($payload['status'], ['SUCCESS', 'COMPLETED'])) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getName(): string
    {
        return 'grabpay';
    }

    protected function getEndpoint(): string
    {
        return $this->config['mode'] === 'live'
            ? 'https://partner-api.grab.com'
            : 'https://partner-api-sandbox.grab.com';
    }

    protected function getAccessToken(): string
    {
        if (!empty($this->clientId) && !empty($this->clientSecret)) {
            $response = Http::timeout(30)
                ->asForm()
                ->withHeaders([
                    'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
                ])
                ->post('https://auth.stg-gka.grab.com/oauth2/token', [
                    'grant_type' => 'client_credentials',
                    'scope' => 'payment',
                ]);

            if ($response->successful()) {
                $result = $response->json();
                return $result['access_token'] ?? '';
            }
        }

        return '';
    }
}
