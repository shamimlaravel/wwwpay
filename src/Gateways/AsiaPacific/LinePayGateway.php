<?php

namespace ShamimStack\WwwPay\Gateways\AsiaPacific;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LinePayGateway implements PaymentGateway
{
    protected $config;
    protected $channelId;
    protected $channelSecret;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->channelId = $config['channel_id'] ?? '';
        $this->channelSecret = $config['channel_secret'] ?? '';
    }

    public function pay(array $data): PaymentResponse
    {
        try {
            if (!isset($data['amount']) || !isset($data['currency']) || !isset($data['order_id'])) {
                throw new PaymentException('Amount, currency, and order_id are required for Line Pay.');
            }

            $orderId = $data['order_id'];
            $amount = $data['amount'];
            $currency = strtoupper($data['currency'] ?? 'JPY');
            $productName = $data['product_name'] ?? 'Product';
            $returnUrl = $data['return_url'] ?? '';
            $confirmUrl = $data['confirm_url'] ?? '';

            $nonce = Str::uuid()->toString();

            $headers = [
                'Content-Type' => 'application/json',
                'X-LINE-ChannelId' => $this->channelId,
                'X-LINE-ChannelSecret' => $this->channelSecret,
                'X-LINE-Authorization-Nonce' => $nonce,
            ];

            $packages = [
                [
                    'id' => 'PACKAGE_' . uniqid(),
                    'name' => $productName,
                    'amount' => (int)round($amount),
                    'products' => [
                        [
                            'id' => 'PRODUCT_' . uniqid(),
                            'name' => $productName,
                            'imageUrl' => $data['product_image_url'] ?? '',
                            'quantity' => 1,
                            'price' => (int)round($amount),
                        ],
                    ],
                ],
            ];

            $payload = [
                'amount' => (int)round($amount),
                'currency' => $currency,
                'orderId' => $orderId,
                'packages' => $packages,
                'options' => [
                    'payment' => [
                        'payType' => 'NORMAL',
                    ],
                ],
            ];

            if ($confirmUrl) {
                $payload['redirectUrls'] = [
                    'confirmUrl' => $confirmUrl,
                    'cancelUrl' => $data['cancel_url'] ?? '',
                ];
            }

            $signature = $this->generateSignature($nonce, json_encode($payload));
            $headers['X-LINE-Authorization'] = $signature;

            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($this->getEndpoint() . '/v3/payments/request', $payload);

            $result = $response->json();

            if (isset($result['returnCode']) && $result['returnCode'] === '0000') {
                $info = $result['info'];
                $paymentUrl = $info['paymentUrl']['web'] ?? $info['paymentUrl'] ?? '';

                return new PaymentResponse(
                    true,
                    $info['transactionId'] ?? $orderId,
                    [
                        'gatewayTransactionId' => $info['transactionId'] ?? null,
                        'data' => [
                            'transaction_id' => $info['transactionId'] ?? null,
                            'payment_url' => $paymentUrl,
                            'confirmation_url' => $confirmUrl,
                        ],
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $result['returnMessage'] ?? 'Line Pay request failed',
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

            $nonce = Str::uuid()->toString();

            $payload = [
                'refundAmount' => $amount !== null ? (int)round($amount) : null,
            ];
            $payload = array_filter($payload, fn($v) => $v !== null);

            $headers = [
                'Content-Type' => 'application/json',
                'X-LINE-ChannelId' => $this->channelId,
                'X-LINE-ChannelSecret' => $this->channelSecret,
                'X-LINE-Authorization-Nonce' => $nonce,
            ];

            $signature = $this->generateSignature($nonce, json_encode($payload));
            $headers['X-LINE-Authorization'] = $signature;

            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($this->getEndpoint() . '/v3/payments/' . $transactionId . '/refund', $payload);

            $result = $response->json();

            if (isset($result['returnCode']) && $result['returnCode'] === '0000') {
                return new PaymentResponse(
                    true,
                    $result['info']['refundTransactionId'] ?? $transactionId,
                    [
                        'gatewayTransactionId' => $result['info']['refundTransactionId'] ?? null,
                        'data' => [
                            'refund_transaction_id' => $result['info']['refundTransactionId'] ?? null,
                            'refund_amount' => $result['info']['refundAmount'] ?? $amount,
                        ],
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $result['returnMessage'] ?? 'Line Pay refund failed',
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

    public function subscribe(array $data): \ShamimStack\WwwPay\Models\Subscription
    {
        return new \ShamimStack\WwwPay\Models\Subscription([
            'gateway' => 'linepay',
            'gateway_subscription_id' => 'sub_linepay_' . uniqid(),
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

            if (isset($payload['result']) && $payload['result'] === 'OK') {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getName(): string
    {
        return 'linepay';
    }

    protected function getEndpoint(): string
    {
        return $this->config['mode'] === 'live'
            ? 'https://api-pay.line.me'
            : 'https://sandbox-api-pay.line.me';
    }

    protected function generateSignature(string $nonce, string $body): string
    {
        $signature = base64_encode(hash_hmac('sha256', $this->channelId . $nonce . $body, $this->channelSecret, true));
        return 'HMAC-SHA256 ' . $signature;
    }
}
