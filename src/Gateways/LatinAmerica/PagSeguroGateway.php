<?php

namespace ShamimStack\AllInOnePayment\Gateways\LatinAmerica;

use ShamimStack\AllInOnePayment\Contracts\PaymentGateway;
use ShamimStack\AllInOnePayment\Contracts\PaymentResponse;
use ShamimStack\AllInOnePayment\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PagSeguroGateway implements PaymentGateway
{
    protected $config;
    protected $email;
    protected $token;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->email = $config['email'] ?? '';
        $this->token = $config['token'] ?? '';
    }

    public function pay(array $data): PaymentResponse
    {
        try {
            if (!isset($data['amount']) || !isset($data['email'])) {
                throw new PaymentException('Amount and email are required for PagSeguro.');
            }

            $reference = $data['order_id'] ?? 'ORDER_' . uniqid();
            $amount = $data['amount'];
            $currency = 'BRL';

            $payload = [
                'email' => $this->email,
                'token' => $this->token,
                'reference' => $reference,
                'senderName' => $data['name'] ?? '',
                'senderEmail' => $data['email'],
                'currency' => $currency,
                'itemId1' => '001',
                'itemDescription1' => $data['description'] ?? 'Product',
                'itemAmount1' => number_format($amount, 2, '.', ''),
                'itemQuantity1' => 1,
                'redirectURL' => $data['return_url'] ?? $this->config['return_url'] ?? '',
            ];

            if (isset($data['phone'])) {
                $payload['senderPhone'] = preg_replace('/[^0-9]/', '', $data['phone']);
            }

            if (isset($data['cpf'])) {
                $payload['senderCPF'] = preg_replace('/[^0-9]/', '', $data['cpf']);
            }

            $response = Http::timeout(30)
                ->asForm()
                ->post($this->getEndpoint() . '/v2/checkout', $payload);

            $result = $response->body();

            if (strpos($result, '<code>') !== false) {
                preg_match('/<code>(.*?)<\/code>/', $result, $codeMatch);
                preg_match('/<date>(.*?)<\/date>/', $result, $dateMatch);

                if (isset($codeMatch[1])) {
                    return new PaymentResponse(
                        true,
                        $codeMatch[1],
                        [
                            'gatewayTransactionId' => $codeMatch[1],
                            'data' => [
                                'code' => $codeMatch[1],
                                'date' => $dateMatch[1] ?? null,
                                'redirect_url' => $this->getCheckoutUrl() . '?code=' . $codeMatch[1],
                            ],
                        ]
                    );
                }
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => 'PagSeguro payment creation failed',
                    'data' => ['response' => $result],
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
                'email' => $this->email,
                'token' => $this->token,
                'transactionCode' => $transactionId,
            ];

            if ($amount !== null) {
                $payload['refundValue'] = number_format($amount, 2, '.', '');
            }

            $response = Http::timeout(30)
                ->asForm()
                ->post($this->getEndpoint() . '/v2/transactions/refunds', $payload);

            $result = $response->json() ?? ['result' => $response->body()];

            if (isset($result['result']) && $result['result'] === 'OK') {
                return new PaymentResponse(
                    true,
                    $transactionId,
                    [
                        'gatewayTransactionId' => $transactionId,
                        'data' => [
                            'status' => 'refunded',
                            'transaction_code' => $transactionId,
                        ],
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $result['error'] ?? 'PagSeguro refund failed',
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
                'email' => $this->email,
                'token' => $this->token,
                'transactionCode' => $transactionId,
            ];

            $response = Http::timeout(30)
                ->asForm()
                ->post($this->getEndpoint() . '/v2/transactions/cancellations', $payload);

            $result = $response->json() ?? ['result' => $response->body()];

            if (isset($result['result']) && $result['result'] === 'OK') {
                return new PaymentResponse(
                    true,
                    $transactionId,
                    [
                        'gatewayTransactionId' => $transactionId,
                        'data' => [
                            'status' => 'cancelled',
                        ],
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $result['error'] ?? 'PagSeguro cancel failed',
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

    public function subscribe(array $data): \ShamimStack\AllInOnePayment\Models\Subscription
    {
        return new \ShamimStack\AllInOnePayment\Models\Subscription([
            'gateway' => 'pagseguro',
            'gateway_subscription_id' => 'sub_pagseguro_' . uniqid(),
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

            if (isset($payload['notificationType']) && $payload['notificationType'] === 'transaction') {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getName(): string
    {
        return 'pagseguro';
    }

    protected function getEndpoint(): string
    {
        return $this->config['mode'] === 'production'
            ? 'https://ws.pagseguro.uol.com.br'
            : 'https://ws.sandbox.pagseguro.uol.com.br';
    }

    protected function getCheckoutUrl(): string
    {
        return $this->config['mode'] === 'production'
            ? 'https://pagseguro.uol.com.br/checkout/v2/payment.html'
            : 'https://sandbox.pagseguro.uol.com.br/checkout/v2/payment.html';
    }
}
