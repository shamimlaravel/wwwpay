<?php

namespace ShamimStack\WwwPay\Gateways\China;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AlipayGateway implements PaymentGateway
{
    protected $config;
    protected $appId;
    protected $privateKey;
    protected $alipayPublicKey;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->appId = $config['app_id'] ?? '';
        $this->privateKey = $config['merchant_private_key'] ?? '';
        $this->alipayPublicKey = $config['alipay_public_key'] ?? '';
    }

    public function pay(array $data): PaymentResponse
    {
        try {
            if (!isset($data['amount']) || !isset($data['subject'])) {
                throw new PaymentException('Amount and subject are required for Alipay.');
            }

            $outTradeNo = $data['order_id'] ?? 'ORDER_' . uniqid();
            $amount = $data['amount'];
            $currency = strtoupper($data['currency'] ?? 'CNY');

            $bizContent = [
                'out_trade_no' => $outTradeNo,
                'total_amount' => $amount,
                'subject' => $data['subject'],
                'product_code' => 'FAST_INSTANT_TRADE_PAY',
                'timeout_express' => $data['timeout'] ?? '30m',
            ];

            if (isset($data['return_url'])) {
                $bizContent['qr_pay_mode'] = '2';
            }

            $payload = [
                'app_id' => $this->appId,
                'method' => 'alipay.trade.app.pay',
                'charset' => 'utf-8',
                'sign_type' => 'RSA2',
                'timestamp' => date('Y-m-d H:i:s'),
                'version' => '1.0',
                'biz_content' => json_encode($bizContent),
                'notify_url' => $data['notify_url'] ?? $this->config['notify_url'] ?? '',
            ];

            $payload['sign'] = $this->generateSignature($payload);

            $response = Http::timeout(30)
                ->asForm()
                ->post($this->getEndpoint() . '/gateway.do', $payload);

            $result = $response->json();

            if (isset($result['alipay_trade_app_pay_response'])) {
                $responseData = $result['alipay_trade_app_pay_response'];

                if ($responseData['code'] === '10000') {
                    return new PaymentResponse(
                        true,
                        $responseData['trade_no'],
                        [
                            'gatewayTransactionId' => $responseData['trade_no'],
                            'data' => [
                                'order_string' => $result['sign'],
                                'out_trade_no' => $outTradeNo,
                                'trade_no' => $responseData['trade_no'],
                            ],
                        ]
                    );
                }

                return new PaymentResponse(
                    false,
                    null,
                    [
                        'errorMessage' => $responseData['sub_msg'] ?? $responseData['msg'] ?? 'Alipay request failed',
                        'data' => $responseData,
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => 'Invalid Alipay response',
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

            $bizContent = [
                'trade_no' => $transactionId,
                'refund_amount' => $amount ?? 0,
                'refund_reason' => 'Customer request',
            ];

            $payload = [
                'app_id' => $this->appId,
                'method' => 'alipay.trade.refund',
                'charset' => 'utf-8',
                'sign_type' => 'RSA2',
                'timestamp' => date('Y-m-d H:i:s'),
                'version' => '1.0',
                'biz_content' => json_encode($bizContent),
            ];

            $payload['sign'] = $this->generateSignature($payload);

            $response = Http::timeout(30)
                ->asForm()
                ->post($this->getEndpoint() . '/gateway.do', $payload);

            $result = $response->json();

            if (isset($result['alipay_trade_refund_response'])) {
                $responseData = $result['alipay_trade_refund_response'];

                if ($responseData['code'] === '10000') {
                    return new PaymentResponse(
                        true,
                        $responseData['trade_no'],
                        [
                            'gatewayTransactionId' => $responseData['trade_no'],
                            'data' => [
                                'trade_no' => $responseData['trade_no'],
                                'out_trade_no' => $responseData['out_trade_no'],
                                'buyer_logon_id' => $responseData['buyer_logon_id'] ?? null,
                            ],
                        ]
                    );
                }

                return new PaymentResponse(
                    false,
                    null,
                    [
                        'errorMessage' => $responseData['sub_msg'] ?? $responseData['msg'] ?? 'Alipay refund failed',
                        'data' => $responseData,
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => 'Invalid Alipay refund response',
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

            $bizContent = [
                'trade_no' => $transactionId,
            ];

            $payload = [
                'app_id' => $this->appId,
                'method' => 'alipay.trade.close',
                'charset' => 'utf-8',
                'sign_type' => 'RSA2',
                'timestamp' => date('Y-m-d H:i:s'),
                'version' => '1.0',
                'biz_content' => json_encode($bizContent),
            ];

            $payload['sign'] = $this->generateSignature($payload);

            $response = Http::timeout(30)
                ->asForm()
                ->post($this->getEndpoint() . '/gateway.do', $payload);

            $result = $response->json();

            if (isset($result['alipay_trade_close_response'])) {
                $responseData = $result['alipay_trade_close_response'];

                if ($responseData['code'] === '10000') {
                    return new PaymentResponse(
                        true,
                        $responseData['trade_no'],
                        [
                            'gatewayTransactionId' => $responseData['trade_no'],
                            'data' => $responseData,
                        ]
                    );
                }

                return new PaymentResponse(
                    false,
                    null,
                    [
                        'errorMessage' => $responseData['sub_msg'] ?? $responseData['msg'] ?? 'Alipay cancel failed',
                        'data' => $responseData,
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => 'Invalid Alipay cancel response',
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

    public function subscribe(array $data): \ShamimStack\WwwPay\Models\Subscription
    {
        return new \ShamimStack\WwwPay\Models\Subscription([
            'gateway' => 'alipay',
            'gateway_subscription_id' => 'sub_alipay_' . uniqid(),
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
            $params = $request->all();
            $signature = $params['sign'] ?? '';

            unset($params['sign'], $params['sign_type']);

            $isValid = $this->verifySignature($params, $signature);

            if (!$isValid) {
                return false;
            }

            if ($params['trade_status'] === 'TRADE_SUCCESS' || $params['trade_status'] === 'TRADE_FINISHED') {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getName(): string
    {
        return 'alipay';
    }

    protected function getEndpoint(): string
    {
        return $this->config['mode'] === 'prod'
            ? 'https://openapi.alipay.com'
            : 'https://openapi-sandbox.dl.alipaydev.com';
    }

    protected function generateSignature(array $params): string
    {
        ksort($params);
        $signString = urldecode(http_build_query($params));
        
        $privateKey = openssl_pkey_get_private($this->privateKey);
        openssl_sign($signString, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        openssl_pkey_free($privateKey);

        return base64_encode($signature);
    }

    protected function verifySignature(array $params, string $signature): bool
    {
        ksort($params);
        $signString = urldecode(http_build_query($params));

        $publicKey = openssl_pkey_get_public($this->alipayPublicKey);
        $result = openssl_verify($signString, base64_decode($signature), $publicKey, OPENSSL_ALGO_SHA256);
        openssl_pkey_free($publicKey);

        return $result === 1;
    }
}
