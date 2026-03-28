<?php

namespace ShamimStack\WwwPay\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use ShamimStack\WwwPay\B2B\B2BPayment;
use ShamimStack\WwwPay\Exceptions\PaymentException;

class B2BController extends Controller
{
    public function createInvoice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email',
            'client_address' => 'nullable|string',
            'due_date' => 'nullable|date|after:today',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        try {
            $invoice = B2BPayment::createInvoice($validated);

            return response()->json([
                'success' => true,
                'invoice' => [
                    'id' => $invoice->getInvoiceId(),
                    'number' => $invoice->getInvoiceNumber(),
                    'amount' => $invoice->getAmount(),
                    'currency' => $invoice->getCurrency(),
                    'status' => $invoice->getStatus(),
                    'client_name' => $validated['client_name'],
                    'due_date' => $validated['due_date'] ?? null,
                    'items' => $validated['items'],
                ],
                'message' => 'Invoice created successfully',
            ]);
        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function sendInvoice(Request $request, string $invoiceId): JsonResponse
    {
        try {
            B2BPayment::sendInvoice($invoiceId);

            return response()->json([
                'success' => true,
                'message' => 'Invoice sent successfully',
            ]);
        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function wireTransfer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'iban' => 'required|string|max:34',
            'bic' => 'nullable|string|max:11',
            'beneficiary_name' => 'required|string|max:255',
            'beneficiary_address' => 'nullable|string',
            'reference' => 'nullable|string|max:140',
            'metadata' => 'nullable|array',
        ]);

        try {
            $transfer = B2BPayment::wireTransfer($validated);

            return response()->json([
                'success' => true,
                'transfer' => [
                    'id' => $transfer->getTransferId(),
                    'amount' => $validated['amount'],
                    'currency' => $validated['currency'],
                    'status' => $transfer->getStatus(),
                    'estimated_arrival' => $transfer->getEstimatedArrival(),
                ],
                'message' => 'Wire transfer initiated successfully',
            ]);
        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function purchaseOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'vendor_name' => 'required|string|max:255',
            'vendor_email' => 'required|email',
            'order_number' => 'required|string|max:50',
            'due_date' => 'nullable|date',
            'items' => 'nullable|array',
            'metadata' => 'nullable|array',
        ]);

        try {
            $order = B2BPayment::createPurchaseOrder($validated);

            return response()->json([
                'success' => true,
                'purchase_order' => [
                    'id' => $order->getOrderId(),
                    'number' => $validated['order_number'],
                    'amount' => $validated['amount'],
                    'currency' => $validated['currency'],
                    'vendor_name' => $validated['vendor_name'],
                    'status' => $order->getStatus(),
                ],
                'message' => 'Purchase order created successfully',
            ]);
        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
