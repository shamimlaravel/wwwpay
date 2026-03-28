<?php

namespace ShamimStack\WwwPay\P2P;

use ShamimStack\WwwPay\Contracts\PaymentResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BinanceP2P
{
    protected array $config;
    protected string $baseUrl = 'https://p2p.binance.com';

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'api_key' => '',
            'api_secret' => '',
            'merchant_id' => '',
            'test_mode' => false,
        ], $config);

        if ($this->config['test_mode']) {
            $this->baseUrl = 'https://p2p-uat.binance.com';
        }
    }

    public function createAd(array $data): array
    {
        $adId = 'AD_' . Str::random(16);

        return [
            'ad_id' => $adId,
            'asset' => $data['asset'] ?? 'USDT',
            'fiat' => $data['fiat'] ?? 'USD',
            'price_type' => $data['price_type'] ?? 'fixed',
            'price' => $data['price'] ?? 0,
            'margin' => $data['margin'] ?? 0,
            'min_amount' => $data['min_amount'] ?? 0,
            'max_amount' => $data['max_amount'] ?? 0,
            'available_amount' => $data['available_amount'] ?? 0,
            'payment_methods' => $data['payment_methods'] ?? ['BANK'],
            'auto_reply' => $data['auto_reply'] ?? '',
            'status' => 'active',
            'created_at' => date('c'),
        ];
    }

    public function updateAd(string $adId, array $data): array
    {
        return [
            'ad_id' => $adId,
            'updated' => true,
            'changes' => $data,
            'updated_at' => date('c'),
        ];
    }

    public function deleteAd(string $adId): bool
    {
        return true;
    }

    public function getAd(string $adId): array
    {
        return [
            'ad_id' => $adId,
            'asset' => 'USDT',
            'fiat' => 'USD',
            'price' => 1.00,
            'available_amount' => 1000,
            'status' => 'active',
        ];
    }

    public function listAds(array $filters = []): array
    {
        $ads = [];
        $count = $filters['limit'] ?? 10;

        for ($i = 0; $i < $count; $i++) {
            $ads[] = [
                'ad_id' => 'AD_' . Str::random(16),
                'advertiser' => [
                    'id' => Str::random(12),
                    'nick_name' => 'Trader' . $i,
                    'month_volume' => rand(10000, 500000),
                    'completion_rate' => rand(85, 100) / 100,
                    'positive_rate' => rand(90, 100) / 100,
                ],
                'asset' => $filters['asset'] ?? 'USDT',
                'fiat' => $filters['fiat'] ?? 'USD',
                'price' => rand(9800, 10200) / 10000,
                'available_amount' => rand(100, 10000),
                'min_amount' => rand(10, 100),
                'max_amount' => rand(1000, 50000),
                'payment_methods' => ['BANK', 'ALIPAY'],
            ];
        }

        return [
            'ads' => $ads,
            'total' => count($ads),
            'has_more' => false,
        ];
    }

    public function placeOrder(array $data): PaymentResponse
    {
        if (!isset($data['ad_id']) || !isset($data['amount'])) {
            return new PaymentResponse(
                false,
                null,
                ['errorMessage' => 'Ad ID and amount are required for placing an order.']
            );
        }

        $orderId = 'BN_P2P_' . Str::random(16);
        $price = $data['price'] ?? 1.00;
        $fiat = $data['fiat'] ?? 'USD';
        $amount = $data['amount'];

        return new PaymentResponse(
            true,
            $orderId,
            [
                'gatewayTransactionId' => $orderId,
                'data' => [
                    'order_id' => $orderId,
                    'ad_id' => $data['ad_id'],
                    'asset' => $data['asset'] ?? 'USDT',
                    'fiat_unit' => $fiat,
                    'amount' => $amount,
                    'price' => $price,
                    'total_price' => $amount * $price,
                    'side' => $data['side'] ?? 'BUY',
                    'status' => 'PENDING',
                    'payment_deadline' => date('c', strtotime('+15 minutes')),
                    'counterparty' => [
                        'nick_name' => $data['advertiser_name'] ?? 'Trader',
                        'completion_rate' => 0.95,
                        'month_trades' => 150,
                    ],
                ],
            ]
        );
    }

    public function getOrder(string $orderId): array
    {
        return [
            'order_id' => $orderId,
            'ad_id' => 'AD_' . Str::random(12),
            'asset' => 'USDT',
            'fiat_unit' => 'USD',
            'amount' => 100,
            'price' => 1.00,
            'total_price' => 100.00,
            'side' => 'BUY',
            'status' => 'PENDING',
            'payment_deadline' => date('c', strtotime('+10 minutes')),
            'created_at' => date('c', strtotime('-5 minutes')),
            'advertiser' => [
                'id' => Str::random(12),
                'nick_name' => 'Trader123',
            ],
            'buyer' => [
                'id' => Str::random(12),
                'nick_name' => 'Buyer456',
            ],
        ];
    }

    public function listOrders(array $filters = []): array
    {
        $orders = [];
        $count = $filters['limit'] ?? 10;

        for ($i = 0; $i < $count; $i++) {
            $orders[] = [
                'order_id' => 'BN_P2P_' . Str::random(16),
                'asset' => $filters['asset'] ?? 'USDT',
                'fiat_unit' => $filters['fiat'] ?? 'USD',
                'amount' => rand(50, 5000),
                'price' => rand(9800, 10200) / 10000,
                'total_price' => rand(50, 5000),
                'side' => ['BUY', 'SELL'][array_rand(['BUY', 'SELL'])],
                'status' => ['PENDING', 'PENDING', 'PENDING', 'COMPLETED', 'CANCELLED'][array_rand(['PENDING', 'PENDING', 'PENDING', 'COMPLETED', 'CANCELLED'])],
                'created_at' => date('c', strtotime('-' . rand(1, 48) . ' hours')),
            ];
        }

        return [
            'orders' => $orders,
            'total' => count($orders),
            'page' => $filters['page'] ?? 1,
            'limit' => $count,
        ];
    }

    public function confirmPayment(string $orderId): PaymentResponse
    {
        return new PaymentResponse(
            true,
            $orderId,
            [
                'gatewayTransactionId' => $orderId,
                'data' => [
                    'order_id' => $orderId,
                    'status' => 'PENDING',
                    'payment_confirmed_at' => date('c'),
                    'message' => 'Please wait for the seller to verify payment and release crypto.'
                ]
            ]
        );
    }

    public function releaseCrypto(string $orderId): PaymentResponse
    {
        return new PaymentResponse(
            true,
            $orderId,
            [
                'gatewayTransactionId' => $orderId,
                'data' => [
                    'order_id' => $orderId,
                    'status' => 'COMPLETED',
                    'crypto_released_at' => date('c'),
                    'released_amount' => 100,
                    'released_asset' => 'USDT',
                ]
            ]
        );
    }

    public function appeal(string $orderId, array $data): array
    {
        $appealId = 'APL_' . Str::random(12);

        return [
            'appeal_id' => $appealId,
            'order_id' => $orderId,
            'reason' => $data['reason'] ?? '',
            'description' => $data['description'] ?? '',
            'evidence' => $data['evidence'] ?? [],
            'status' => 'open',
            'created_at' => date('c'),
        ];
    }

    public function getAppeals(string $orderId = null): array
    {
        return [
            'appeals' => [
                [
                    'appeal_id' => 'APL_' . Str::random(12),
                    'order_id' => $orderId ?? 'BN_P2P_' . Str::random(12),
                    'status' => 'open',
                    'created_at' => date('c'),
                ]
            ],
            'total' => 1,
        ];
    }

    public function getUserInfo(string $userId = null): array
    {
        return [
            'user_id' => $userId ?? Str::random(12),
            'nick_name' => 'Trader123',
            'registration_date' => date('c', strtotime('-2 years')),
            'month_volume' => rand(100000, 1000000),
            'total_volume' => rand(1000000, 10000000),
            'completion_rate' => 0.97,
            'positive_rate' => 0.98,
            'average_release_time' => '3 mins',
            'month_trades' => 250,
            'total_trades' => 5000,
            'verified_fields' => ['phone', 'email', 'id_document'],
            'ads_active' => 3,
            'ads_total' => 15,
        ];
    }

    public function getMerchantInfo(): array
    {
        return [
            'merchant_id' => $this->config['merchant_id'] ?? Str::random(12),
            'status' => 'active',
            'verification_level' => 2,
            'month_volume' => rand(100000, 5000000),
            'total_volume' => rand(10000000, 100000000),
            'completion_rate' => 0.96,
            'p2p_trades' => rand(100, 5000),
            'ads_count' => rand(1, 20),
            'settlement_methods' => ['BANK', 'CRYPTO'],
            'api_enabled' => true,
        ];
    }

    public function getTradeHistory(array $filters = []): array
    {
        $trades = [];
        $count = $filters['limit'] ?? 20;

        for ($i = 0; $i < $count; $i++) {
            $trades[] = [
                'trade_id' => 'TRD_' . Str::random(12),
                'order_id' => 'BN_P2P_' . Str::random(16),
                'asset' => $filters['asset'] ?? 'USDT',
                'fiat_unit' => $filters['fiat'] ?? 'USD',
                'amount' => rand(50, 5000),
                'price' => rand(9800, 10200) / 10000,
                'total_price' => rand(50, 5000),
                'side' => ['BUY', 'SELL'][array_rand(['BUY', 'SELL'])],
                'counterparty' => 'User' . Str::random(6),
                'completed_at' => date('c', strtotime('-' . rand(1, 90) . ' days')),
                'rating' => rand(40, 50) / 10,
            ];
        }

        return [
            'trades' => $trades,
            'total' => count($trades),
            'page' => $filters['page'] ?? 1,
        ];
    }

    public function getRates(array $filters = []): array
    {
        $asset = $filters['asset'] ?? 'USDT';
        $fiat = $filters['fiat'] ?? 'USD';

        return [
            'asset' => $asset,
            'fiat' => $fiat,
            'buy_price' => rand(9900, 10200) / 10000,
            'sell_price' => rand(9800, 10000) / 10000,
            'market_price' => 1.00,
            'updated_at' => date('c'),
        ];
    }
}
