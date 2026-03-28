<?php

namespace ShamimStack\WwwPay\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use ShamimStack\WwwPay\Security\FraudDetection;

class VerifyPaymentSecurity
{
    protected FraudDetection $fraudDetection;

    public function __construct(FraudDetection $fraudDetection)
    {
        $this->fraudDetection = $fraudDetection;
    }

    public function handle(Request $request, Closure $next)
    {
        $fraudData = [
            'amount' => $request->input('amount'),
            'card_country' => $request->input('card_country'),
            'ip_country' => $request->ip(),
            'user_id' => $request->user()?->id,
            'email' => $request->input('email'),
        ];

        $result = $this->fraudDetection->analyze($fraudData);

        if ($result->isHighRisk()) {
            return response()->json([
                'success' => false,
                'error' => 'Transaction flagged for review',
                'code' => 'FRAUD_DETECTED',
            ], 403);
        }

        if ($result->getRiskScore() > 70) {
            $request->merge(['requires_verification' => true]);
        }

        return $next($request);
    }
}
