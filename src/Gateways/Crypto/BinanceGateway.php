<?php

namespace ShamimStack\WwwPay\Gateways\Crypto;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Contracts\Subscription;
use ShamimStack\WwwPay\Traits\HasPayments;
use ShamimStack\WwwPay\P2P\BinanceP2P;
use ShamimStack\WwwPay\B2B\BinanceB2B;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class BinanceGateway implements PaymentGateway
{
    use HasPayments;

    protected array $config;
    protected string $baseUrl = 'https://api.binance.com';
    protected ?BinanceP2P $p2pService = null;
    protected ?BinanceB2B $b2bService = null;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $amount = $data['amount'] ?? 0;
        $currency = strtoupper($data['currency'] ?? 'USDT');
        $fiat = strtoupper($data['fiat'] ?? 'USD');
        $side = $data['side'] ?? 'BUY';
        $paymentMethod = $data['payment_method'] ?? 'BANK';

        try {
            $orderId = 'BN_' . Str::random(16);
            $advertiserId = $data['advertiser_id'] ?? null;
            $price = $data['price'] ?? $this->getMarketPrice($currency, $fiat);

            $payload = [
                'orderId' => $orderId,
                'advertiserId' => $advertiserId,
                'asset' => $currency,
                'fiatUnit' => $fiat,
                'amount' => $amount,
                'price' => $price,
                'totalPrice' => $amount * $price,
                'side' => $side,
                'paymentMethod' => $paymentMethod,
                'status' => 'PENDING',
            ];

            return new PaymentResponse(
                true,
                $orderId,
                [
                    'gatewayTransactionId' => $orderId,
                    'data' => $payload,
                ]
            );
        } catch (\Exception $e) {
            return new PaymentResponse(
                false,
                null,
                ['errorMessage' => 'Binance P2P payment failed: ' . $e->getMessage()]
            );
        }
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        $refundId = 'BN_REF_' . Str::random(12);

        return new PaymentResponse(
            true,
            $refundId,
            [
                'gatewayTransactionId' => $transactionId,
                'data' => [
                    'original_order_id' => $transactionId,
                    'refund_id' => $refundId,
                    'status' => 'refund_initiated',
                    'note' => 'Contact support for Binance P2P disputes'
                ],
            ]
        );
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(
            true,
            $transactionId,
            [
                'gatewayTransactionId' => $transactionId,
                'data' => [
                    'order_id' => $transactionId,
                    'status' => 'cancelled'
                ]
            ]
        );
    }

    public function verify(array $data): bool
    {
        if (isset($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        if (isset($data['phone']) && strlen($data['phone']) >= 10) {
            return true;
        }
        if (isset($data['binance_id']) && !empty($data['binance_id'])) {
            return true;
        }
        return false;
    }

    public function getName(): string
    {
        return 'Binance';
    }

    public function supportsSubscriptions(): bool
    {
        return true;
    }

    public function subscribe(array $data): Subscription
    {
        return new class($data) implements Subscription {
            private string $subscriptionId;
            private ?string $planId;
            private ?string $customerId;
            private string $status;
            private ?\DateTimeInterface $endDate;
            private array $extraData;

            public function __construct(array $data)
            {
                $this->subscriptionId = 'BN_SUB_' . Str::random(12);
                $this->planId = $data['plan_id'] ?? 'p2p_recurring';
                $this->customerId = $data['customer_id'] ?? '';
                $this->status = 'active';
                $this->endDate = new \DateTime('+1 month');
                $this->extraData = [
                    'type' => 'p2p_recurring',
                    'asset' => $data['asset'] ?? 'USDT',
                    'fiat' => $data['fiat'] ?? 'USD',
                    'amount' => $data['amount'] ?? 0,
                    'frequency' => $data['frequency'] ?? 'monthly'
                ];
            }

            public function getGatewaySubscriptionId(): ?string
            {
                return $this->subscriptionId;
            }

            public function getPlanId(): ?string
            {
                return $this->planId;
            }

            public function getCustomerId(): ?string
            {
                return $this->customerId;
            }

            public function getStatus(): string
            {
                return $this->status;
            }

            public function isActive(): bool
            {
                return $this->status === 'active';
            }

            public function isCancelled(): bool
            {
                return $this->status === 'cancelled';
            }

            public function isExpired(): bool
            {
                return $this->endDate !== null && $this->endDate < new \DateTime();
            }

            public function cancel(?string $reason = null): bool
            {
                $this->status = 'cancelled';
                return true;
            }

            public function getStartDate(): ?\DateTimeInterface
            {
                return new \DateTime();
            }

            public function getEndDate(): ?\DateTimeInterface
            {
                return $this->endDate;
            }
        };
    }

    public function unsubscribe(string $subscriptionId): bool
    {
        return true;
    }

    public function handleWebhook($request): bool
    {
        $payload = $request->all();
        
        if (!isset($payload['eventType']) && !isset($payload['type'])) {
            $payload = json_decode($request->getContent(), true) ?? [];
        }

        $eventType = $payload['eventType'] ?? $payload['type'] ?? null;

        return match($eventType) {
            'P2P_ORDER_CREATED' => $this->handleOrderCreated($payload),
            'P2P_ORDER_PAID' => $this->handleOrderPaid($payload),
            'P2P_ORDER_RELEASED' => $this->handleOrderReleased($payload),
            'P2P_ORDER_CANCELLED' => $this->handleOrderCancelled($payload),
            'P2P_ORDER_APPEALED' => $this->handleOrderAppealed($payload),
            'P2P_ORDER_DISPUTE' => $this->handleOrderDispute($payload),
            default => false
        };
    }

    protected function handleOrderCreated(array $payload): bool
    {
        return isset($payload['orderId']);
    }

    protected function handleOrderPaid(array $payload): bool
    {
        return isset($payload['orderId']);
    }

    protected function handleOrderReleased(array $payload): bool
    {
        return isset($payload['orderId']);
    }

    protected function handleOrderCancelled(array $payload): bool
    {
        return isset($payload['orderId']);
    }

    protected function handleOrderAppealed(array $payload): bool
    {
        return isset($payload['orderId']);
    }

    protected function handleOrderDispute(array $payload): bool
    {
        return isset($payload['orderId']);
    }

    protected function getMarketPrice(string $crypto, string $fiat): float
    {
        $prices = [
            'USDT_USD' => 1.00,
            'USDT_BRL' => 4.97,
            'USDT_PHP' => 56.50,
            'USDT_VND' => 24500,
            'USDT_NGN' => 1550,
            'BTC_USD' => 67500,
            'ETH_USD' => 3450,
            'BNB_USD' => 580,
        ];

        $key = "{$crypto}_{$fiat}";
        return $prices[$key] ?? 1.0;
    }

    public function p2p(): BinanceP2P
    {
        if ($this->p2pService === null) {
            $this->p2pService = new BinanceP2P($this->config);
        }
        return $this->p2pService;
    }

    public function b2b(): BinanceB2B
    {
        if ($this->b2bService === null) {
            $this->b2bService = new BinanceB2B($this->config);
        }
        return $this->b2bService;
    }
}
