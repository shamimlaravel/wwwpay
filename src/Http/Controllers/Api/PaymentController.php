<?php

namespace ShamimStack\WwwPay\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use ShamimStack\WwwPay\Facades\Payment;
use ShamimStack\WwwPay\Exceptions\PaymentException;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $gateways = Payment::getAvailableGateways();
        
        return response()->json([
            'success' => true,
            'gateways' => $gateways,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'gateway' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'email' => 'nullable|email',
            'return_url' => 'nullable|url',
            'cancel_url' => 'nullable|url',
            'metadata' => 'nullable|array',
        ]);

        try {
            $gateway = $validated['gateway'] ?? config('payment.default_gateway');
            unset($validated['gateway']);

            $response = Payment::gateway($gateway)->pay($validated);

            return response()->json([
                'success' => $response->isSuccessful() || $response->isPending(),
                'action' => $response->isRedirect() ? 'redirect' : 'complete',
                'transaction_id' => $response->getTransactionId(),
                'redirect_url' => $response->getRedirectUrl(),
                'message' => $response->getMessage(),
                'data' => $response->toArray(),
            ]);
        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ], 422);
        }
    }

    public function show(string $transactionId): JsonResponse
    {
        try {
            $response = Payment::verify($transactionId);

            return response()->json([
                'success' => true,
                'transaction' => $response->toArray(),
            ]);
        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    public function verify(Request $request): JsonResponse
    {
        $transactionId = $request->query('transaction_id');
        
        if (!$transactionId) {
            return response()->json([
                'success' => false,
                'error' => 'Transaction ID is required',
            ], 400);
        }

        try {
            $response = Payment::verify($transactionId);

            return response()->json([
                'success' => $response->isSuccessful(),
                'verified' => true,
                'transaction' => $response->toArray(),
            ]);
        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    public function refund(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'transaction_id' => 'required|string',
            'amount' => 'nullable|numeric|min:0.01',
            'reason' => 'nullable|string',
        ]);

        try {
            $response = Payment::refund(
                $validated['transaction_id'],
                $validated['amount'] ?? null,
                ['reason' => $validated['reason'] ?? null]
            );

            return response()->json([
                'success' => $response->isSuccessful(),
                'refund_id' => $response->getTransactionId(),
                'message' => 'Refund processed successfully',
            ]);
        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function gateways(Request $request): JsonResponse
    {
        $region = $request->query('region');
        $gateways = Payment::getAvailableGateways();

        if ($region) {
            $gateways = array_filter($gateways, function ($gateway) use ($region) {
                return $gateway['region'] === $region;
            });
        }

        return response()->json([
            'success' => true,
            'gateways' => array_values($gateways),
            'regions' => config('payment.regions', []),
        ]);
    }

    public function testGateway(string $gateway): JsonResponse
    {
        try {
            $result = Payment::gateway($gateway)->test();

            return response()->json([
                'success' => $result['success'],
                'gateway' => $gateway,
                'message' => $result['message'] ?? 'Gateway is configured correctly',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'gateway' => $gateway,
                'error' => $e->getMessage(),
            ], 503);
        }
    }
}
