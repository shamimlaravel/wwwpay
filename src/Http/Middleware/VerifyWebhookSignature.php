<?php

namespace ShamimStack\WwwPay\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyWebhookSignature
{
    public function handle(Request $request, Closure $next, string $gateway)
    {
        if (!method_exists($this, 'verify' . ucfirst($gateway))) {
            return $next($request);
        }

        $verified = call_user_func([$this, 'verify' . ucfirst($gateway)], $request);

        if (!$verified) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid webhook signature',
            ], 401);
        }

        return $next($request);
    }

    protected function verifyStripe(Request $request): bool
    {
        $signature = $request->header('Stripe-Signature');
        $webhookSecret = config('payment.gateways.stripe.webhook_secret');

        if (!$signature || !$webhookSecret) {
            return false;
        }

        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }

    protected function verifyPaypal(Request $request): bool
    {
        $signature = $request->header('PAYPAL-TRANSMISSION-SIG');
        return !empty($signature);
    }

    protected function verifyPaystack(Request $request): bool
    {
        $signature = $request->header('x-paystack-signature');
        $webhookSecret = config('payment.gateways.paystack.webhook_secret');

        if (!$signature || !$webhookSecret) {
            return false;
        }

        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha512', $payload, $webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }

    protected function verifyFlutterwave(Request $request): bool
    {
        $signature = $request->header('verif-hash');
        $webhookSecret = config('payment.gateways.flutterwave.webhook_secret');

        if (!$signature || !$webhookSecret) {
            return false;
        }

        return hash_equals($signature, $webhookSecret);
    }
}
