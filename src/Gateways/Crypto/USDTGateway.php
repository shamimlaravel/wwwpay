<?php

namespace ShamimStack\WwwPay\Gateways\Crypto;

use ShamimStack\WwwPay\Contracts\PaymentGateway;
use ShamimStack\WwwPay\Contracts\PaymentResponse;
use ShamimStack\WwwPay\Traits\HasPayments;
use Illuminate\Support\Str;

class USDTGateway implements PaymentGateway
{
    use HasPayments;

    protected array $config;
    protected string $network;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->network = $config['network'] ?? 'TRC20';
    }

    public function pay(array $data): PaymentResponse
    {
        $amount = $data['amount'] ?? 0;
        $currency = 'USDT';
        $walletAddress = $this->config['wallet_address'] ?? '';
        
        $transactionId = 'USDT_' . Str::random(24);
        $paymentAddress = $this->generatePaymentAddress($walletAddress);
        
        return new \ShamimStack\WwwPay\Responses\PaymentResponse(
            true,
            $transactionId,
            $transactionId,
            "Send {$amount} USDT to address: {$paymentAddress}",
            $amount,
            $currency,
            [
                'payment_address' => $paymentAddress,
                'network' => $this->network,
                'qr_code' => $this->generateQRCode($paymentAddress, $amount),
                'confirmations_needed' => 6
            ]
        );
    }

    protected function generatePaymentAddress(string $baseAddress): string
    {
        return $baseAddress;
    }

    protected function generateQRCode(string $address, float $amount): string
    {
        return "usdt:{$address}?amount={$amount}";
    }

    public function refund(string $transactionId, float $amount = null): PaymentResponse
    {
        return new \ShamimStack\WwwPay\Responses\PaymentResponse(
            false,
            '',
            $transactionId,
            'Cryptocurrency refunds are not supported. Please contact support.'
        );
    }

    public function cancel(string $transactionId): PaymentResponse
    {
        return new \ShamimStack\WwwPay\Responses\PaymentResponse(
            false,
            '',
            $transactionId,
            'Cryptocurrency transactions cannot be cancelled once confirmed.'
        );
    }

    public function verify(array $data): bool
    {
        $address = $data['wallet_address'] ?? '';
        
        if ($this->network === 'TRC20') {
            return strlen($address) === 34 && str_starts_with($address, 'T');
        }
        
        return strlen($address) === 42 && str_starts_with($address, '0x');
    }

    public function getName(): string
    {
        return 'USDT';
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
        return isset($payload['txid']) && isset($payload['to']);
    }

    public function getNetwork(): string
    {
        return $this->network;
    }

    public function confirmPayment(string $transactionId): bool
    {
        return true;
    }
}
