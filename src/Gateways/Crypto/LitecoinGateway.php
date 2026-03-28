<?php

namespace ShamimStack\WwwPay\Gateways\Crypto;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class LitecoinGateway implements PaymentGateway
{
    use HasPayments;

    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function pay(array $data): PaymentResponse
    {
        $amount = $data['amount'] ?? 0;
        $currency = 'LTC';
        $walletAddress = $this->config['wallet_address'] ?? '';
        
        $transactionId = 'LTC_' . Str::random(24);
        
        return new \ShamimStack\WwwPay\Responses\PaymentResponse(
            true,
            $transactionId,
            $transactionId,
            "Send {$amount} LTC to address: {$walletAddress}",
            $amount,
            $currency,
            [
                'payment_address' => $walletAddress,
                'network' => 'Litecoin',
                'qr_code' => "litecoin:{$walletAddress}?amount={$amount}",
                'confirmations_needed' => 6
            ]
        );
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new \ShamimStack\WwwPay\Responses\PaymentResponse(
            false,
            '',
            $transactionId,
            'Cryptocurrency refunds are not supported.'
        );
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new \ShamimStack\WwwPay\Responses\PaymentResponse(
            false,
            '',
            $transactionId,
            'Cryptocurrency transactions cannot be cancelled.'
        );
    }

    public function verify(array $data): bool
    {
        $address = $data['wallet_address'] ?? '';
        $isMainnet = str_starts_with($address, 'L') || str_starts_with($address, 'M');
        $isTestnet = str_starts_with($address, 't') || str_starts_with($address, '2');
        return strlen($address) >= 26 && strlen($address) <= 35 && ($isMainnet || $isTestnet);
    }

    public function getName(): string
    {
        return 'Litecoin';
    }

    public function supportsSubscriptions(): bool
    {
        return false;
    }

    public function subscribe(array $data)
    {
        throw new \Exception('Cryptocurrency subscriptions are not supported');
    }

    public function unsubscribe(string $subscriptionId): bool
    {
        return false;
    }

    public function handleWebhook($request): bool
    {
        $payload = $request->all();
        return isset($payload['txid']);
    }
}
