<?php

namespace ShamimStack\AllInOnePayment\Gateways\China;

use ShamimStack\AllInOnePayment\Contracts\PaymentGateway;
use ShamimStack\AllInOnePayment\Contracts\PaymentResponse;
use ShamimStack\AllInOnePayment\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class WeChatPayGateway implements PaymentGateway
{
    protected $config;
    protected $apiKey;
    protected $mchId;
    protected $appId;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->apiKey = $config['api_key'] ?? $config['merchant_private_key'] ?? '';
        $this->mchId = $config['mch_id'] ?? '';
        $this->appId = $config['app_id'] ?? '';
    }

    public function pay(array $data): PaymentResponse
    {
        try {
            if (!isset($data['amount']) || !isset($data['description'])) {
                throw new PaymentException('Amount and description are required for WeChat Pay.');
            }

            $orderId = $data['order_id'] ?? 'ORDER_' . uniqid();
            $amount = (int)round($data['amount'] * 100);
            $currency = $data['currency'] ?? 'CNY';
            $openId = $data['open_id'] ?? null;

            if ($currency !== 'CNY') {
                throw new PaymentException('WeChat Pay primarily supports CNY currency.');
            }

            $payload = [
                'appid' => $this->appId,
                'mch_id' => $this->mchId,
                'nonce_str' => Str::random(32),
                'body' => $data['description'],
                'out_trade_no' => $orderId,
                'total_fee' => $amount,
                'spbill_create_ip' => $data['client_ip'] ?? request()->ip(),
                'notify_url' => $data['notify_url'] ?? $this->config['notify_url'] ?? '',
                'trade_type' => $openId ? 'JSAPI' : 'NATIVE',
            ];

            if ($openId) {
                $payload['openid'] = $openId;
            }

            $payload['sign'] = $this->generateSignature($payload);

            $response = Http::timeout(30)
                ->asXml()
                ->withHeaders(['Content-Type' => 'application/xml'])
                ->post($this->getEndpoint() . '/pay/unifiedorder', $payload);

            $result = $this->parseXmlResponse($response->body());

            if ($result['return_code'] === 'SUCCESS' && $result['result_code'] === 'SUCCESS') {
                if ($openId) {
                    return new PaymentResponse(
                        true,
                        $result['prepay_id'],
                        [
                            'gatewayTransactionId' => $result['prepay_id'],
                            'data' => [
                                'prepay_id' => $result['prepay_id'],
                                'order_id' => $orderId,
                                'code_url' => null,
                                'app_info' => $this->buildJsApiParams($result['prepay_id']),
                            ],
                        ]
                    );
                }

                return new PaymentResponse(
                    true,
                    $result['prepay_id'],
                    [
                        'gatewayTransactionId' => $result['prepay_id'],
                        'data' => [
                            'prepay_id' => $result['prepay_id'],
                            'order_id' => $orderId,
                            'code_url' => $result['code_url'] ?? null,
                        ],
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $result['err_code_des'] ?? $result['return_msg'] ?? 'WeChat Pay request failed',
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

            $payload = [
                'appid' => $this->appId,
                'mch_id' => $this->mchId,
                'nonce_str' => Str::random(32),
                'transaction_id' => $transactionId,
                'out_refund_no' => 'REFUND_' . uniqid(),
                'total_fee' => isset($amount) ? (int)round($amount * 100) : null,
                'refund_fee' => isset($amount) ? (int)round($amount * 100) : null,
            ];

            $payload = array_filter($payload, fn($v) => $v !== null);
            $payload['sign'] = $this->generateSignature($payload);

            $response = Http::timeout(30)
                ->asXml()
                ->withHeaders(['Content-Type' => 'application/xml'])
                ->post($this->getEndpoint() . '/secapi/pay/refund', $payload);

            $result = $this->parseXmlResponse($response->body());

            if ($result['return_code'] === 'SUCCESS' && $result['result_code'] === 'SUCCESS') {
                return new PaymentResponse(
                    true,
                    $result['refund_id'],
                    [
                        'gatewayTransactionId' => $result['refund_id'],
                        'data' => [
                            'refund_id' => $result['refund_id'],
                            'out_refund_no' => $result['out_refund_no'],
                            'refund_fee' => $result['refund_fee'] / 100,
                            'total_fee' => $result['total_fee'] / 100,
                        ],
                    ]
                );
            }

            return new PaymentResponse(
                false,
                null,
                [
                    'errorMessage' => $result['err_code_des'] ?? $result['return_msg'] ?? 'WeChat Pay refund failed',
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
        return new PaymentResponse(
            false,
            $transactionId,
            [
                'errorMessage' => 'WeChat Pay does not support direct cancellation. Use refund instead.',
                'data' => [
                    'suggestion' => 'Use refund() method to reverse completed transactions.',
                ],
            ]
        );
    }

    public function subscribe(array $data): \ShamimStack\AllInOnePayment\Models\Subscription
    {
        return new \ShamimStack\AllInOnePayment\Models\Subscription([
            'gateway' => 'wechat',
            'gateway_subscription_id' => 'sub_wechat_' . uniqid(),
            'status' => 'active',
            'plan_id' => $data['plan_id'] ?? null,
            'customer_id' => $data['open_id'] ?? $data['customer_id'] ?? null,
            'start_date' => now(),
            'end_date' => null,
        ]);
    }

    public function handleWebhook(\Illuminate\Http\Request $request): bool
    {
        try {
            $payload = $request->all();
            $signature = $payload['sign'] ?? '';

            unset($payload['sign']);
            $calculatedSignature = $this->generateSignature($payload);

            if ($signature !== $calculatedSignature) {
                return false;
            }

            if ($payload['return_code'] === 'SUCCESS') {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getName(): string
    {
        return 'wechat';
    }

    protected function getEndpoint(): string
    {
        return $this->config['mode'] === 'prod'
            ? 'https://api.mch.weixin.qq.com'
            : 'https://api.mch.weixin.qq.com';
    }

    protected function generateSignature(array $data): string
    {
        ksort($data);
        $signString = urldecode(http_build_query($data)) . '&key=' . $this->apiKey;
        return strtoupper(md5($signString));
    }

    protected function parseXmlResponse(string $xml): array
    {
        $result = [];
        $xmlParser = xml_parser_create();
        xml_parse_into_struct($xmlParser, $xml, $values, $keys);
        xml_parser_free($xmlParser);

        foreach ($keys as $key => $vals) {
            $result[$key] = $values[$vals[0]]['value'] ?? null;
        }

        return $result;
    }

    protected function buildJsApiParams(string $prepayId): array
    {
        $params = [
            'appId' => $this->appId,
            'timeStamp' => (string)time(),
            'nonceStr' => Str::random(32),
            'package' => 'prepay_id=' . $prepayId,
            'signType' => 'MD5',
        ];

        $params['paySign'] = $this->generateSignature($params);

        return $params;
    }
}
