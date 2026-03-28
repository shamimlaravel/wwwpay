<?php

namespace ShamimStack\WwwPay\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use ShamimStack\WwwPay\P2P\P2PPayment;
use ShamimStack\WwwPay\Exceptions\PaymentException;

class P2PController extends Controller
{
    public function sendMoney(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'recipient_id' => 'required|string',
            'recipient_name' => 'nullable|string',
            'note' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
        ]);

        try {
            $transfer = P2PPayment::sendMoney($validated);

            return response()->json([
                'success' => true,
                'transfer' => [
                    'id' => $transfer->getTransferId(),
                    'amount' => $validated['amount'],
                    'currency' => $validated['currency'],
                    'recipient_id' => $validated['recipient_id'],
                    'status' => $transfer->getStatus(),
                    'completed_at' => $transfer->getCompletedAt(),
                ],
                'message' => 'Money sent successfully',
            ]);
        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function requestMoney(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'from_user' => 'required|string',
            'message' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
        ]);

        try {
            $request = P2PPayment::requestMoney($validated);

            return response()->json([
                'success' => true,
                'request' => [
                    'id' => $request->getRequestId(),
                    'amount' => $validated['amount'],
                    'currency' => $validated['currency'],
                    'from_user' => $validated['from_user'],
                    'status' => $request->getStatus(),
                    'expires_at' => $request->getExpiresAt(),
                ],
                'message' => 'Money request sent',
            ]);
        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function splitPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'total_amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'participants' => 'required|array|min:2',
            'participants.*.user_id' => 'required|string',
            'participants.*.amount' => 'nullable|numeric',
            'split_equally' => 'nullable|boolean',
            'description' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        try {
            $split = P2PPayment::splitPayment($validated);

            return response()->json([
                'success' => true,
                'split' => [
                    'id' => $split->getSplitId(),
                    'total_amount' => $validated['total_amount'],
                    'currency' => $validated['currency'],
                    'participants' => $split->getParticipants(),
                    'status' => $split->getStatus(),
                ],
                'message' => 'Payment split created',
            ]);
        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function createEscrow(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'released_to' => 'required|string',
            'released_on' => 'nullable|date|after:now',
            'description' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        try {
            $escrow = P2PPayment::escrow($validated);

            return response()->json([
                'success' => true,
                'escrow' => [
                    'id' => $escrow->getEscrowId(),
                    'amount' => $validated['amount'],
                    'currency' => $validated['currency'],
                    'released_to' => $validated['released_to'],
                    'status' => $escrow->getStatus(),
                    'release_date' => $escrow->getReleaseDate(),
                ],
                'message' => 'Escrow created successfully',
            ]);
        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function releaseEscrow(string $escrowId): JsonResponse
    {
        try {
            $escrow = P2PPayment::releaseEscrow($escrowId);

            return response()->json([
                'success' => true,
                'escrow' => [
                    'id' => $escrowId,
                    'status' => $escrow->getStatus(),
                    'released_at' => $escrow->getReleasedAt(),
                ],
                'message' => 'Escrow released successfully',
            ]);
        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
