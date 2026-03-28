<?php

namespace ShamimStack\WwwPay\Gateways\Crypto;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class USDCGateway implements PaymentGateway
{
    use HasPayments;

    protected array $config;
    protected string $network;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->network = $config['network'] ?? 'ERC20';
    }

    public function pay(array $data): PaymentResponse
    {
        $amount = $data['amount'] ?? 0;
        $currency = 'USDC';
        $walletAddress = $this->config['wallet_address'] ?? '';
        
        $transactionId = 'USDC_' . Str::random(24);
        $paymentAddress = $walletAddress;
        
        return new \ShamimStack\WwwPay\Responses\PaymentResponse(
            true,
            $transactionId,
            $transactionId,
            "Send {$amount} USDC to address: {$paymentAddress}",
            $amount,
            $currency,
            [
                'payment_address' => $paymentAddress,
                'network' => $this->network,
                'qr_code' => "usdc:{$paymentAddress}?amount={$amount}",
                'confirmations_needed' => 12
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
        return strlen($address) === 42 && str_starts_with($address, '0x');
    }

    public function getName(): string
    {
        return 'USDC';
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
        return isset($payload['tx_hash']);
    }

    public function getNetwork(): string
    {
        return $this->network;
    }
}
