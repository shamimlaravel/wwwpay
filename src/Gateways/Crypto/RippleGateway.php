<?php

namespace ShamimStack\WwwPay\Gateways\Crypto;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class RippleGateway implements PaymentGateway
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
        $currency = 'XRP';
        $walletAddress = $this->config['wallet_address'] ?? '';
        $destinationTag = $this->config['destination_tag'] ?? Str::random(8);
        
        $transactionId = 'XRP_' . Str::random(24);
        
        return new \ShamimStack\WwwPay\Responses\PaymentResponse(
            true,
            $transactionId,
            $transactionId,
            "Send {$amount} XRP to address: {$walletAddress} (Tag: {$destinationTag})",
            $amount,
            $currency,
            [
                'payment_address' => $walletAddress,
                'destination_tag' => $destinationTag,
                'network' => 'XRP Ledger',
                'qr_code' => "xrp:{$walletAddress}?dt={$destinationTag}&amount={$amount}",
                'confirmations_needed' => 1
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
            'Ripple transactions cannot be cancelled after submission.'
        );
    }

    public function verify(array $data): bool
    {
        $address = $data['wallet_address'] ?? '';
        return strlen($address) >= 25 && strlen($address) <= 35 && 
               (str_starts_with($address, 'r') || str_starts_with($address, 'R'));
    }

    public function getName(): string
    {
        return 'Ripple (XRP)';
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
        return isset($payload['hash']);
    }
}
