<?php

namespace ShamimStack\WwwPay\Gateways\Africa;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;

class FlutterwaveGateway implements PaymentGateway
{
    protected $config;
    protected $publicKey;
    protected $secretKey;
    protected $encryptionKey;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->publicKey = $config['public_key'] ?? '';
        $this->secretKey = $config['secret_key'] ?? '';
        $this->encryptionKey = $config['encryption_key'] ?? '';
    }

    public function pay(array $data): PaymentResponse
    {
        try {
            if (!isset($data['amount']) || !isset($data['email'])) {
                throw new PaymentException('Amount and email are required for Flutterwave payment.');
            }

            $txRef = $data['order_id'] ?? 'ORDER_' . uniqid();
            $amount = $data['amount'];
            $currency = strtoupper($data['currency'] ?? 'NGN');

            $payload = [
                'tx_ref' => $txRef,
                'amount' => $amount,
                'currency' => $currency,
                'payment_options' => $data['payment_options'] ?? 'card,ussd,mobilemoney',
                'redirect_url' => $data['return_url'] ?? $this->config['return_url'] ?? '',
                'customer' => [
                    'email' => $data['email'],
                    'phonenumber' => $data['phone'] ?? '',
                    'name' => $data['name'] ?? $data['customer_name'] ?? '',
                ],
                'customizations' => [
                    'title' => $data['title'] ?? 'Payment',
                    'description' => $data['description'] ?? '',
                    'logo' => $data['logo'] ?? '',
                ],
            ];

            $headers = [
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ];

            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($this->getEndpoint() . '/v3/payments', $payload);

            $result = $response->json();

            if (isset($result['status']) && $result['status'] === 'success') {
                return new PaymentResponse(
                    true,
                    $result['data']['tx_ref'],
                    [
                        'gatewayTransactionId' => $result['data']['id'] ?? null,
                        'data' => [
                            'link' => $result['data']['link'] ?? null,
                            'tx_ref' => $result['data']['tx_ref'] ?? null,
                            'flw_ref' => $result['data']['flw_ref'] ?? null,
                        ],
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $result['message'] ?? 'Flutterwave payment failed',
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
                'id' => $transactionId,
            ];

            if ($amount !== null) {
                $payload['amount'] = $amount;
            }

            $headers = [
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ];

            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($this->getEndpoint() . '/v3/refunds', $payload);

            $result = $response->json();

            if (isset($result['status']) && $result['status'] === 'success') {
                return new PaymentResponse(
                    true,
                    (string)$result['data']['id'],
                    [
                        'gatewayTransactionId' => (string)$result['data']['id'],
                        'data' => [
                            'id' => $result['data']['id'] ?? null,
                            'amount' => $result['data']['amount'] ?? null,
                            'currency' => $result['data']['currency'] ?? null,
                            'status' => $result['data']['status'] ?? null,
                        ],
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $result['message'] ?? 'Flutterwave refund failed',
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
                'errorMessage' => 'Flutterwave does not support direct cancellation. Use refund instead.',
                'data' => [
                    'suggestion' => 'Use refund() to reverse the transaction.',
                ],
            ]
        );
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Models\Subscription
    {
        return new \ShamimStack\WwwPay\Models\Subscription([
            'gateway' => 'flutterwave',
            'gateway_subscription_id' => 'sub_fw_' . uniqid(),
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
            $secretHash = $this->config['webhook_secret'] ?? '';
            $signature = $request->header('Verif-Hash');

            if ($secretHash && $signature !== $secretHash) {
                return false;
            }

            $payload = $request->all();

            if (isset($payload['event']) && $payload['event'] === 'charge.completed') {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getName(): string
    {
        return 'flutterwave';
    }

    protected function getEndpoint(): string
    {
        return $this->config['mode'] === 'live'
            ? 'https://api.flutterwave.com'
            : 'https://api.flutterwave.com';
    }
}
