<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Data\Payment\GatewayQueryData;
use App\Data\Payment\PaymentInitiateData;
use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    public function gateways(GatewayQueryData $data): JsonResponse
    {
        $gateways = $this->paymentService->getAvailableGateways($data->city, $data->module);

        return response()->json([
            'success' => true,
            'message' => 'Available payment gateways.',
            'data' => $gateways,
        ]);
    }

    public function initiate(PaymentInitiateData $data, Request $request): JsonResponse
    {
        $response = $this->paymentService->initiatePayment($data, (string) $request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $response,
        ], 201);
    }

    public function callback(string $gateway, Request $request): JsonResponse
    {
        $this->paymentService->handleCallback($gateway, $request->all());

        return response()->json([
            'success' => true,
            'message' => 'Callback processed successfully',
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $transactions = $this->paymentService->getTransactionsForUser((string) $request->user()->id, $perPage);

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    public function show(string $id, Request $request): JsonResponse
    {
        $transaction = $this->paymentService->getTransactionForUser($id, (string) $request->user()->id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $transaction,
        ]);
    }
}
