<?php

namespace ShamimStack\WwwPay\B2B;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class B2BPayment
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'default_gateway' => 'stripe',
        ], $config);
    }

    public function createInvoice(array $data)
    {
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . Str::random(6);
        
        return new class($data, $invoiceNumber) {
            private array $data;
            private string $invoiceNumber;
            private string $invoiceId;

            public function __construct(array $data, string $invoiceNumber)
            {
                $this->data = $data;
                $this->invoiceNumber = $invoiceNumber;
                $this->invoiceId = 'INV_' . Str::random(16);
            }

            public function getInvoiceId(): string
            {
                return $this->invoiceId;
            }

            public function getInvoiceNumber(): string
            {
                return $this->invoiceNumber;
            }

            public function getAmount(): float
            {
                return $this->data['amount'] ?? 0;
            }

            public function getCurrency(): string
            {
                return $this->data['currency'] ?? 'USD';
            }

            public function getStatus(): string
            {
                return 'draft';
            }

            public function getDueDate(): ?string
            {
                return $this->data['due_date'] ?? null;
            }

            public function toArray(): array
            {
                return [
                    'invoice_id' => $this->invoiceId,
                    'invoice_number' => $this->invoiceNumber,
                    'amount' => $this->getAmount(),
                    'currency' => $this->getCurrency(),
                    'status' => $this->getStatus(),
                    'due_date' => $this->getDueDate(),
                ];
            }
        };
    }

    public function sendInvoice(string $invoiceId, array $data): PaymentResponse
    {
        return new PaymentResponse(
            true,
            $invoiceId,
            [
                'gatewayTransactionId' => $invoiceId,
                'data' => [
                    'invoice_id' => $invoiceId,
                    'sent_to' => $data['recipient_email'] ?? '',
                    'sent_at' => date('c'),
                ],
            ]
        );
    }

    public function payInvoice(string $invoiceId, array $paymentData): PaymentResponse
    {
        $gateway = $paymentData['gateway'] ?? $this->config['default_gateway'];
        
        return new PaymentResponse(
            true,
            'TX_' . Str::random(16),
            [
                'gatewayTransactionId' => $invoiceId,
                'data' => [
                    'invoice_id' => $invoiceId,
                    'gateway' => $gateway,
                    'paid_at' => date('c'),
                    'status' => 'paid',
                ],
            ]
        );
    }

    public function createPurchaseOrder(array $data)
    {
        return new class($data) {
            private array $data;
            private string $orderId;

            public function __construct(array $data)
            {
                $this->data = $data;
                $this->orderId = 'PO_' . Str::random(16);
            }

            public function getOrderId(): string
            {
                return $this->orderId;
            }

            public function getStatus(): string
            {
                return 'pending';
            }

            public function toArray(): array
            {
                return [
                    'order_id' => $this->orderId,
                    'vendor_name' => $this->data['vendor_name'] ?? '',
                    'amount' => $this->data['amount'] ?? 0,
                    'currency' => $this->data['currency'] ?? 'USD',
                    'status' => $this->getStatus(),
                ];
            }
        };
    }

    public function wireTransfer(array $data): PaymentResponse
    {
        if (!isset($data['amount'])) {
            return new PaymentResponse(
                false,
                null,
                ['errorMessage' => 'Amount is required for wire transfer.']
            );
        }

        return new PaymentResponse(
            true,
            'WIRE_' . Str::random(16),
            [
                'gatewayTransactionId' => 'WIRE_' . Str::random(16),
                'data' => [
                    'method' => 'wire_transfer',
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? 'USD',
                    'reference' => 'REF_' . Str::random(12),
                    'estimated_days' => 3,
                    'instructions' => $this->getWireInstructions($data),
                ],
            ]
        );
    }

    public function processWireTransfer(array $data): PaymentResponse
    {
        return $this->wireTransfer($data);
    }

    public function processACHPayment(array $data): PaymentResponse
    {
        if (!isset($data['amount']) || !isset($data['routing_number']) || !isset($data['account_number'])) {
            return new PaymentResponse(
                false,
                null,
                ['errorMessage' => 'Amount, routing number, and account number are required for ACH.']
            );
        }

        return new PaymentResponse(
            true,
            'ACH_' . Str::random(16),
            [
                'gatewayTransactionId' => 'ACH_' . Str::random(16),
                'data' => [
                    'method' => 'ach',
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? 'USD',
                    'status' => 'processing',
                    'estimated_days' => 2,
                ],
            ]
        );
    }

    public function processCorporateCard(array $data): PaymentResponse
    {
        if (!isset($data['amount']) || !isset($data['card_number'])) {
            return new PaymentResponse(
                false,
                null,
                ['errorMessage' => 'Amount and card number are required.']
            );
        }

        return new PaymentResponse(
            true,
            'CORP_' . Str::random(16),
            [
                'gatewayTransactionId' => 'CORP_' . Str::random(16),
                'data' => [
                    'method' => 'corporate_card',
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? 'USD',
                    'last_four' => substr($data['card_number'], -4),
                    'card_type' => $this->detectCardType($data['card_number']),
                ],
            ]
        );
    }

    public function processPurchaseCard(array $data): PaymentResponse
    {
        return $this->processCorporateCard($data);
    }

    public function processVendorPayment(array $data): PaymentResponse
    {
        if (!isset($data['amount']) || !isset($data['vendor_id'])) {
            return new PaymentResponse(
                false,
                null,
                ['errorMessage' => 'Amount and vendor ID are required.']
            );
        }

        return new PaymentResponse(
            true,
            'VENDOR_' . Str::random(16),
            [
                'gatewayTransactionId' => 'VENDOR_' . Str::random(16),
                'data' => [
                    'method' => 'vendor_payment',
                    'vendor_id' => $data['vendor_id'],
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? 'USD',
                    'reference' => $data['reference'] ?? '',
                ],
            ]
        );
    }

    public function processInteracB2B(array $data): PaymentResponse
    {
        if (!isset($data['amount'])) {
            return new PaymentResponse(
                false,
                null,
                ['errorMessage' => 'Amount is required for Interac B2B.']
            );
        }

        return new PaymentResponse(
            true,
            'INTERAC_' . Str::random(16),
            [
                'gatewayTransactionId' => 'INTERAC_' . Str::random(16),
                'data' => [
                    'method' => 'interac_b2b',
                    'amount' => $data['amount'],
                    'currency' => 'CAD',
                    'email' => $data['email'] ?? '',
                ],
            ]
        );
    }

    public function processConcentratedFunds(array $data): PaymentResponse
    {
        return $this->wireTransfer($data);
    }

    public function requestPaymentLink(array $data): array
    {
        $linkId = 'PL_' . Str::random(16);
        
        return [
            'link_id' => $linkId,
            'url' => $this->generatePaymentLink($linkId, $data),
            'expires_at' => date('c', strtotime('+' . ($data['expiry_days'] ?? 7) . ' days')),
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'USD',
            'description' => $data['description'] ?? '',
        ];
    }

    public function bulkPayment(array $payments): array
    {
        $batchId = 'BATCH_' . Str::random(16);
        $results = [];
        $totalAmount = 0;
        $successCount = 0;
        $failCount = 0;

        foreach ($payments as $payment) {
            $response = $this->processVendorPayment($payment);
            $results[] = [
                'reference' => $payment['reference'] ?? '',
                'status' => $response->isSuccessful() ? 'success' : 'failed',
                'transaction_id' => $response->getTransactionId(),
            ];

            if ($response->isSuccessful()) {
                $successCount++;
                $totalAmount += $payment['amount'] ?? 0;
            } else {
                $failCount++;
            }
        }

        return [
            'batch_id' => $batchId,
            'total_payments' => count($payments),
            'successful' => $successCount,
            'failed' => $failCount,
            'total_amount' => $totalAmount,
            'results' => $results,
            'processed_at' => date('c'),
        ];
    }

    protected function getWireInstructions(array $data): array
    {
        return [
            'bank_name' => $data['bank_name'] ?? '',
            'account_name' => $data['account_name'] ?? '',
            'account_number' => $data['account_number'] ?? '',
            'routing_number' => $data['routing_number'] ?? '',
            'iban' => $data['iban'] ?? '',
            'swift_code' => $data['swift_code'] ?? '',
            'reference' => 'REF_' . Str::random(12),
        ];
    }

    protected function generatePaymentLink(string $linkId, array $data): string
    {
        return "https://pay.example.com/b2b/{$linkId}";
    }

    protected function detectCardType(string $cardNumber): string
    {
        $number = preg_replace('/[^0-9]/', '', $cardNumber);
        
        if (preg_match('/^4/', $number)) return 'visa';
        if (preg_match('/^5[1-5]/', $number)) return 'mastercard';
        if (preg_match('/^3[47]/', $number)) return 'amex';
        if (preg_match('/^6(?:011|5[0-9]{2})/', $number)) return 'discover';
        
        return 'unknown';
    }
}
