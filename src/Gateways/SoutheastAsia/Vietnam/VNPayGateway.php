<?php

namespace ShamimStack\WwwPay\Gateways\SoutheastAsia\Vietnam;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class VNPayGateway implements PaymentGateway
{
    use HasPayments;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $transactionId = 'VNPAY_' . Str::random(12);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? $transactionId;
        $orderInfo = $data['description'] ?? 'VNPay Payment';

        $vnp_Url = $this->config['vnp_Url'] ?? 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';
        $vnp_Returnurl = $data['return_url'] ?? url('/payment/callback/vnpay');
        $vnp_TmnCode = $this->config['vnp_TmnCode'] ?? '';
        $vnp_HashSecret = $this->config['vnp_HashSecret'] ?? '';

        $vnp_TxnRef = $orderId;
        $vnp_OrderInfo = $orderInfo;
        $vnp_Amount = $amount * 100;
        $vnp_Locale = $data['locale'] ?? 'vn';
        $vnp_BankCode = $data['bank_code'] ?? '';
        $vnp_IpAddr = $data['ip'] ?? request()->ip();

        $params = [
            'vnp_Amount' => $vnp_Amount,
            'vnp_Command' => 'pay',
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => $vnp_IpAddr,
            'vnp_Locale' => $vnp_Locale,
            'vnp_OrderInfo' => $vnp_OrderInfo,
            'vnp_ReturnUrl' => $vnp_Returnurl,
            'vnp_TmnCode' => $vnp_TmnCode,
            'vnp_TxnRef' => $vnp_TxnRef,
            'vnp_Version' => '2.1.0',
        ];

        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'vnpay',
            'gatewayTransactionId' => $transactionId,
            'orderId' => $orderId,
            'amount' => $amount,
            'currency' => 'VND',
            'paymentUrl' => $vnp_Url,
            'params' => $params,
            'status' => 'PENDING',
        ]);
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new PaymentResponse(true, 'REF_VNPAY_' . Str::random(12), [
            'gateway' => 'vnpay',
            'originalTransactionId' => $transactionId,
            'refundAmount' => $amount,
            'status' => 'REFUNDED',
        ]);
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new PaymentResponse(true, $transactionId, [
            'gateway' => 'vnpay',
            'status' => 'CANCELLED',
        ]);
    }

    public function verify(array $data): bool
    {
        return isset($data['vnp_TxnRef']) || isset($data['orderId']);
    }

    public function getName(): string
    {
        return 'vnpay';
    }

    public function supportsSubscriptions(): bool
    {
        return false;
    }

    public function subscribe(array $data): \ShamimStack\WwwPay\Contracts\Subscription
    {
        throw new \Exception('VNPay does not support subscriptions');
    }

    public function unsubscribe(string $subscriptionId): bool
    {
        return false;
    }

    public function handleWebhook($request): bool
    {
        $data = $request->all();
        return isset($data['vnp_TxnRef']) && isset($data['vnp_ResponseCode']);
    }
}
