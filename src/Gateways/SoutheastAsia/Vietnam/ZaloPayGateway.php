<?php

namespace ShamimStack\WwwPay\Gateways\SoutheastAsia\Vietnam;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class ZaloPayGateway implements PaymentGateway
{
    use HasPayments;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $transactionId = 'ZALO_' . Str::random(12);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? $transactionId;
        $description = $data['description'] ?? 'ZaloPay Payment';

        $appId = $this->config['app_id'] ?? '';
        $appUser = $data['app_user'] ?? 'user';
        $appTime = time();
        $embedData = json_encode(['redirecturl' => $data['return_url'] ?? url('/payment/callback/zalopay')]);
        $item = json_encode([['item' => [['itemid' => '1', 'itemname' => $description, 'itemquantity' => 1]]]]);

        $params = [
            'app_id' => $appId,
            'app_user' => $appUser,
            'app_time' => $appTime,
            'amount' => $amount,
            'app_trans_id' => date('ymdHis') . '_' . rand(111111, 999999),
            'embed_data' => $embedData,
            'item' => $item,
            'description' => $description,
            'bank_code' => $data['bank_code'] ?? '',
        ];

        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'zalopay',
            'gatewayTransactionId' => $transactionId,
            'orderId' => $orderId,
            'amount' => $amount,
            'currency' => $data['currency'] ?? 'VND',
            'params' => $params,
            'status' => 'PENDING',
        ]);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_ZALO_' . Str::random(12), [
            'gateway' => 'zalopay',
            'originalTransactionId' => $transactionId,
            'refundAmount' => $amount,
            'status' => 'REFUNDED',
        ]);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'zalopay',
            'status' => 'CANCELLED',
        ]);
    }

    public function verify(array $data): bool
    {
        return isset($data['app_trans_id']) || isset($data['orderId']);
    }

    public function getName(): string
    {
        return 'zalopay';
    }

    public function supportsSubscriptions(): bool
    {
        return false;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        throw new \Exception('ZaloPay does not support subscriptions');
    }

    public function unsubscribe(string $subscriptionId): bool
    {
        return false;
    }

    public function handleWebhook($request): bool
    {
        $data = $request->all();
        return isset($data['appid']) && isset($data['apptrans_id']);
    }
}
