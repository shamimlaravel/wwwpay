<?php

namespace ShamimStack\WwwPay\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use ShamimStack\WwwPay\Facades\Payment;
use ShamimStack\WwwPay\Exceptions\PaymentException;

class SubscriptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|string',
            'status' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $subscriptions = Payment::getSubscriptions($validated);

        return response()->json([
            'success' => true,
            'subscriptions' => $subscriptions,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'gateway' => 'nullable|string',
            'plan_id' => 'required|string',
            'customer_email' => 'required|email',
            'customer_id' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        try {
            $gateway = $validated['gateway'] ?? config('payment.default_gateway');
            unset($validated['gateway']);

            $subscription = Payment::gateway($gateway)->subscribe($validated);

            return response()->json([
                'success' => true,
                'subscription' => [
                    'id' => $subscription->getSubscriptionId(),
                    'plan_id' => $subscription->getPlanId(),
                    'status' => $subscription->getStatus(),
                    'current_period_end' => $subscription->getCurrentPeriodEnd(),
                ],
                'message' => 'Subscription created successfully',
            ]);
        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(string $subscriptionId): JsonResponse
    {
        try {
            $subscription = Payment::getSubscription($subscriptionId);

            return response()->json([
                'success' => true,
                'subscription' => $subscription->toArray(),
            ]);
        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    public function update(Request $request, string $subscriptionId): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => 'nullable|string',
            'quantity' => 'nullable|integer|min:1',
            'metadata' => 'nullable|array',
        ]);

        try {
            $subscription = Payment::updateSubscription($subscriptionId, $validated);

            return response()->json([
                'success' => true,
                'subscription' => $subscription->toArray(),
                'message' => 'Subscription updated successfully',
            ]);
        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function cancel(Request $request, string $subscriptionId): JsonResponse
    {
        $validated = $request->validate([
            'cancel_at_period_end' => 'nullable|boolean',
        ]);

        try {
            $cancelAtPeriodEnd = $validated['cancel_at_period_end'] ?? false;
            $subscription = Payment::cancelSubscription($subscriptionId, $cancelAtPeriodEnd);

            return response()->json([
                'success' => true,
                'subscription' => $subscription->toArray(),
                'message' => $cancelAtPeriodEnd 
                    ? 'Subscription will be cancelled at end of billing period'
                    : 'Subscription cancelled immediately',
            ]);
        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function pause(string $subscriptionId): JsonResponse
    {
        try {
            $subscription = Payment::pauseSubscription($subscriptionId);

            return response()->json([
                'success' => true,
                'subscription' => $subscription->toArray(),
                'message' => 'Subscription paused',
            ]);
        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function resume(string $subscriptionId): JsonResponse
    {
        try {
            $subscription = Payment::resumeSubscription($subscriptionId);

            return response()->json([
                'success' => true,
                'subscription' => $subscription->toArray(),
                'message' => 'Subscription resumed',
            ]);
        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
