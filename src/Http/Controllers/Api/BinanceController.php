<?php

namespace ShamimStack\WwwPay\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use ShamimStack\WwwPay\Gateways\Crypto\BinanceGateway;
use ShamimStack\WwwPay\P2P\BinanceP2P;
use ShamimStack\WwwPay\B2B\BinanceB2B;
use ShamimStack\WwwPay\Exceptions\PaymentException;

class BinanceController extends Controller
{
    protected BinanceGateway $gateway;
    protected BinanceP2P $p2p;
    protected BinanceB2B $b2b;

    public function __construct()
    {
        $this->gateway = new BinanceGateway(config('payment.gateways.binance', []));
        $this->p2p = new BinanceP2P(config('payment.gateways.binance', []));
        $this->b2b = new BinanceB2B(config('payment.gateways.binance', []));
    }

    public function pay(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|max:10',
            'fiat' => 'nullable|string|max:10',
            'side' => 'nullable|in:BUY,SELL',
            'payment_method' => 'nullable|string',
            'advertiser_id' => 'nullable|string',
            'price' => 'nullable|numeric',
        ]);

        try {
            $response = $this->gateway->pay($validated);

            return response()->json([
                'success' => $response->isSuccessful(),
                'order_id' => $response->getTransactionId(),
                'message' => $response->getMessage(),
                'data' => $response->getData(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function refund(Request $request, string $orderId): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0.01',
        ]);

        try {
            $response = $this->gateway->refund($orderId, $validated['amount'] ?? null);

            return response()->json([
                'success' => $response->isSuccessful(),
                'refund_id' => $response->getTransactionId(),
                'message' => $response->getMessage(),
                'data' => $response->getData(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function cancel(string $orderId): JsonResponse
    {
        try {
            $response = $this->gateway->cancel($orderId);

            return response()->json([
                'success' => $response->isSuccessful(),
                'message' => $response->getMessage(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function webhook(Request $request): JsonResponse
    {
        $result = $this->gateway->handleWebhook($request);

        return response()->json([
            'success' => $result,
            'received_at' => now()->toIso8601String(),
        ]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => 'nullable|string',
            'customer_id' => 'nullable|string',
            'asset' => 'nullable|string',
            'fiat' => 'nullable|string',
            'amount' => 'nullable|numeric',
            'frequency' => 'nullable|string|in:weekly,monthly,quarterly',
        ]);

        try {
            $subscription = $this->gateway->subscribe($validated);

            return response()->json([
                'success' => true,
                'subscription_id' => $subscription->getGatewaySubscriptionId(),
                'status' => $subscription->getStatus(),
                'next_billing' => $subscription->getEndDate(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function unsubscribe(string $subscriptionId): JsonResponse
    {
        $result = $this->gateway->unsubscribe($subscriptionId);

        return response()->json([
            'success' => $result,
            'message' => $result ? 'Subscription cancelled' : 'Failed to cancel subscription',
        ]);
    }

    public function p2pCreateAd(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asset' => 'required|string|max:10',
            'fiat' => 'required|string|max:10',
            'price_type' => 'nullable|in:fixed,market',
            'price' => 'required|numeric|min:0',
            'margin' => 'nullable|numeric',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'available_amount' => 'required|numeric|min:0',
            'payment_methods' => 'nullable|array',
            'auto_reply' => 'nullable|string|max:500',
        ]);

        try {
            $ad = $this->p2p->createAd($validated);

            return response()->json([
                'success' => true,
                'ad' => $ad,
                'message' => 'P2P advertisement created successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function p2pUpdateAd(Request $request, string $adId): JsonResponse
    {
        $validated = $request->validate([
            'price' => 'nullable|numeric|min:0',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'available_amount' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,paused,closed',
        ]);

        try {
            $ad = $this->p2p->updateAd($adId, $validated);

            return response()->json([
                'success' => true,
                'ad' => $ad,
                'message' => 'P2P advertisement updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function p2pDeleteAd(string $adId): JsonResponse
    {
        $result = $this->p2p->deleteAd($adId);

        return response()->json([
            'success' => $result,
            'message' => $result ? 'P2P advertisement deleted' : 'Failed to delete',
        ]);
    }

    public function p2pGetAd(string $adId): JsonResponse
    {
        $ad = $this->p2p->getAd($adId);

        return response()->json([
            'success' => true,
            'ad' => $ad,
        ]);
    }

    public function p2pListAds(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asset' => 'nullable|string|max:10',
            'fiat' => 'nullable|string|max:10',
            'payment_method' => 'nullable|string',
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $result = $this->p2p->listAds($validated);

        return response()->json([
            'success' => true,
            'ads' => $result['ads'],
            'total' => $result['total'],
            'has_more' => $result['has_more'],
        ]);
    }

    public function p2pPlaceOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ad_id' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'asset' => 'nullable|string',
            'fiat' => 'nullable|string',
            'side' => 'nullable|in:BUY,SELL',
            'price' => 'nullable|numeric',
        ]);

        try {
            $response = $this->p2p->placeOrder($validated);

            return response()->json([
                'success' => $response->isSuccessful(),
                'order_id' => $response->getTransactionId(),
                'message' => $response->getMessage(),
                'data' => $response->getData(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function p2pGetOrder(string $orderId): JsonResponse
    {
        $order = $this->p2p->getOrder($orderId);

        return response()->json([
            'success' => true,
            'order' => $order,
        ]);
    }

    public function p2pListOrders(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'nullable|string',
            'side' => 'nullable|in:BUY,SELL',
            'asset' => 'nullable|string',
            'fiat' => 'nullable|string',
            'start_time' => 'nullable|integer',
            'end_time' => 'nullable|integer',
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $result = $this->p2p->listOrders($validated);

        return response()->json([
            'success' => true,
            'orders' => $result['orders'],
            'total' => $result['total'],
            'page' => $result['page'],
        ]);
    }

    public function p2pConfirmPayment(string $orderId): JsonResponse
    {
        try {
            $response = $this->p2p->confirmPayment($orderId);

            return response()->json([
                'success' => $response->isSuccessful(),
                'message' => $response->getMessage(),
                'data' => $response->getData(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function p2pReleaseCrypto(string $orderId): JsonResponse
    {
        try {
            $response = $this->p2p->releaseCrypto($orderId);

            return response()->json([
                'success' => $response->isSuccessful(),
                'message' => $response->getMessage(),
                'data' => $response->getData(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function p2pAppeal(Request $request, string $orderId): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|in:payment_not_received,payment_not_verified,crypto_not_released,other',
            'description' => 'nullable|string|max:1000',
            'evidence' => 'nullable|array',
        ]);

        try {
            $appeal = $this->p2p->appeal($orderId, $validated);

            return response()->json([
                'success' => true,
                'appeal' => $appeal,
                'message' => 'Appeal submitted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function p2pGetAppeals(Request $request, string $orderId = null): JsonResponse
    {
        $result = $this->p2p->getAppeals($orderId);

        return response()->json([
            'success' => true,
            'appeals' => $result['appeals'],
            'total' => $result['total'],
        ]);
    }

    public function p2pGetUserInfo(Request $request): JsonResponse
    {
        $userId = $request->input('user_id');
        $info = $this->p2p->getUserInfo($userId);

        return response()->json([
            'success' => true,
            'user' => $info,
        ]);
    }

    public function p2pGetMerchantInfo(): JsonResponse
    {
        $info = $this->p2p->getMerchantInfo();

        return response()->json([
            'success' => true,
            'merchant' => $info,
        ]);
    }

    public function p2pGetTradeHistory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asset' => 'nullable|string',
            'fiat' => 'nullable|string',
            'side' => 'nullable|in:BUY,SELL',
            'start_time' => 'nullable|integer',
            'end_time' => 'nullable|integer',
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $result = $this->p2p->getTradeHistory($validated);

        return response()->json([
            'success' => true,
            'trades' => $result['trades'],
            'total' => $result['total'],
            'page' => $result['page'],
        ]);
    }

    public function p2pGetRates(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asset' => 'nullable|string',
            'fiat' => 'nullable|string',
        ]);

        $rates = $this->p2p->getRates($validated);

        return response()->json([
            'success' => true,
            'rates' => $rates,
        ]);
    }

    public function b2bCreateMerchantAccount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|string|max:50',
            'registration_number' => 'nullable|string|max:50',
            'website' => 'nullable|url',
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        try {
            $merchant = $this->b2b->createMerchantAccount($validated);

            return response()->json([
                'success' => true,
                'merchant' => $merchant,
                'message' => 'Merchant account created successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function b2bGetMerchantAccount(): JsonResponse
    {
        $merchant = $this->b2b->getMerchantAccount();

        return response()->json([
            'success' => true,
            'merchant' => $merchant,
        ]);
    }

    public function b2bCreatePaymentLink(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'fiat' => 'nullable|string|max:10',
            'description' => 'nullable|string|max:500',
            'expiry_days' => 'nullable|integer|min:1|max:365',
            'redirect_url' => 'nullable|url',
        ]);

        try {
            $link = $this->b2b->createPaymentLink($validated);

            return response()->json([
                'success' => true,
                'payment_link' => $link,
                'message' => 'Payment link created successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function b2bGetPaymentLink(string $linkId): JsonResponse
    {
        $link = $this->b2b->getPaymentLink($linkId);

        return response()->json([
            'success' => true,
            'payment_link' => $link,
        ]);
    }

    public function b2bListPaymentLinks(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'nullable|in:active,paused,expired',
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $result = $this->b2b->listPaymentLinks($validated);

        return response()->json([
            'success' => true,
            'payment_links' => $result['links'],
            'total' => $result['total'],
        ]);
    }

    public function b2bDeletePaymentLink(string $linkId): JsonResponse
    {
        $result = $this->b2b->deletePaymentLink($linkId);

        return response()->json([
            'success' => $result,
            'message' => $result ? 'Payment link deleted' : 'Failed to delete',
        ]);
    }

    public function b2bProcessBulkPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payments' => 'required|array|min:1|max:1000',
            'payments.*.recipient_id' => 'required|string',
            'payments.*.amount' => 'required|numeric|min:0.01',
            'payments.*.currency' => 'nullable|string|max:10',
            'currency' => 'nullable|string|max:10',
        ]);

        try {
            $result = $this->b2b->processBulkPayment($validated);

            return response()->json([
                'success' => true,
                'batch' => $result,
                'message' => 'Bulk payment processed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function b2bGetBulkPaymentStatus(string $batchId): JsonResponse
    {
        $status = $this->b2b->getBulkPaymentStatus($batchId);

        return response()->json([
            'success' => true,
            'batch' => $status,
        ]);
    }

    public function b2bGetSettlement(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fiat' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'nullable|in:pending,processing,completed',
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $result = $this->b2b->getSettlement($validated);

        return response()->json([
            'success' => true,
            'settlements' => $result['settlements'],
            'pending_amount' => $result['pending_amount'],
            'available_amount' => $result['available_amount'],
        ]);
    }

    public function b2bRequestWithdrawal(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|max:10',
            'fiat_amount' => 'nullable|numeric',
            'fiat_currency' => 'nullable|string|max:10',
            'fee' => 'nullable|numeric',
            'payment_method' => 'required|string|in:BANK,CRYPTO',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
        ]);

        try {
            $withdrawal = $this->b2b->requestWithdrawal($validated);

            return response()->json([
                'success' => true,
                'withdrawal' => $withdrawal,
                'message' => 'Withdrawal request submitted',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function b2bGetTransactionHistory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'nullable|in:p2p_order,settlement,withdrawal,deposit',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'fiat' => 'nullable|string',
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $result = $this->b2b->getTransactionHistory($validated);

        return response()->json([
            'success' => true,
            'transactions' => $result['transactions'],
            'total' => $result['total'],
            'total_volume' => $result['total_volume'],
            'page' => $result['page'],
        ]);
    }

    public function b2bGetWalletBalance(): JsonResponse
    {
        $balance = $this->b2b->getWalletBalance();

        return response()->json([
            'success' => true,
            'balance' => $balance,
        ]);
    }

    public function b2bCreateWebhook(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'events' => 'required|array|min:1',
            'events.*' => 'string',
        ]);

        try {
            $webhook = $this->b2b->createWebhook($validated);

            return response()->json([
                'success' => true,
                'webhook' => $webhook,
                'message' => 'Webhook created successfully. Store your secret securely.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function b2bGetWebhookEvents(): JsonResponse
    {
        $events = $this->b2b->getWebhookEvents();

        return response()->json([
            'success' => true,
            'events' => $events['events'],
        ]);
    }

    public function b2bGetApiKeys(): JsonResponse
    {
        $keys = $this->b2b->getApiKeys();

        return response()->json([
            'success' => true,
            'api_keys' => $keys,
        ]);
    }
}
