<?php

namespace ShamimStack\WwwPay\Gateways\Europe;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SEPAGateway implements PaymentGateway
{
    protected $config;
    protected $apiKey;
    protected $iban;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->apiKey = $config['api_key'] ?? '';
        $this->iban = $config['iban'] ?? '';
    }

    public function pay(array $data): PaymentResponse
    {
        try {
            if (!isset($data['amount']) || !isset($data['iban']) || !isset($data['name'])) {
                throw new PaymentException('Amount, iban, and name are required for SEPA payment.');
            }

            $mandateId = 'MANDATE_' . uniqid();
            $endToEndId = 'E2E_' . uniqid();

            $payload = [
                'amount' => [
                    'value' => (int)round($data['amount'] * 100),
                    'currency' => strtoupper($data['currency'] ?? 'EUR'),
                ],
                'debtor' => [
                    'name' => $data['name'],
                    'iban' => $data['iban'],
                ],
                'creditor' => [
                    'name' => $this->config['creditor_name'] ?? '',
                    'iban' => $this->iban,
                ],
                'remittanceInformation' => [
                    'unstructured' => $data['description'] ?? 'Payment',
                ],
                'endToEndId' => $endToEndId,
            ];

            $headers = [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Idempotency-Key' => $endToEndId,
            ];

            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($this->getEndpoint() . '/v1/payment-requests', $payload);

            $result = $response->json();

            if (isset($result['paymentRequestId'])) {
                return new PaymentResponse(
                    true,
                    $result['paymentRequestId'],
                    [
                        'gatewayTransactionId' => $result['paymentRequestId'],
                        'data' => [
                            'mandate_id' => $result['mandateId'] ?? $mandateId,
                            'end_to_end_id' => $endToEndId,
                            'status' => $result['status'] ?? 'PENDING',
                        ],
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $result['errorMessage'] ?? $result['message'] ?? 'SEPA payment request failed',
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
        return new PaymentResponse(
            false,
            null,
            [
                'errorMessage' => 'SEPA does not support instant refunds. Refunds must be initiated manually by your bank.',
                'data' => [
                    'suggestion' => 'Contact your bank to process a SEPA refund.',
                ],
            ]
        );
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(
            false,
            $transactionId,
            [
                'errorMessage' => 'SEPA payments cannot be cancelled once initiated. Contact your bank for assistance.',
                'data' => [
                    'suggestion' => 'Contact your bank to stop the SEPA payment.',
                ],
            ]
        );
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Models\Subscription
    {
        return new \ShamimStack\WwwPay\Models\Subscription([
            'gateway' => 'sepa',
            'gateway_subscription_id' => 'sub_sepa_' . uniqid(),
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

            if (isset($payload['event']) && $payload['event'] === 'payment.settled') {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getName(): string
    {
        return 'sepa';
    }

    protected function getEndpoint(): string
    {
        return $this->config['mode'] === 'live'
            ? 'https://api.sepa.eu'
            : 'https://api.sandbox.sepa.eu';
    }
}
