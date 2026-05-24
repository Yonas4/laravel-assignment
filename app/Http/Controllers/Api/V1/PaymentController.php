<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Data\Payment\GatewayQueryData;
use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;

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
            'data' => [
                'gateways' => $gateways,
            ],
        ]);
    }

    public function initiate(\App\Data\Payment\PaymentInitiateData $data, \Illuminate\Http\Request $request): JsonResponse
    {
        $response = $this->paymentService->initiatePayment($data, (string) $request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }

    public function callback(string $gateway, \Illuminate\Http\Request $request): JsonResponse
    {
        $this->paymentService->handleCallback($gateway, $request->all());

        return response()->json([
            'success' => true,
            'message' => 'Callback processed successfully',
        ]);
    }

    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $transactions = $this->paymentService->getTransactionsForUser((string) $request->user()->id, $perPage);

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    public function show(string $id, \Illuminate\Http\Request $request): JsonResponse
    {
        // Actually we should ideally have findForUser in repository but let's just use Eloquent for this simple read
        // Or inject PaymentTransactionRepository to be strict. I'll just use the model here as a quick read,
        // Wait, rule A-1: "Controllers never touch Eloquent directly". I must use Repository.
        // Let's rely on PaymentService or Repository. I will inject Repository via App() or define find method in Service.
        
        $transaction = app(\App\Repositories\Contracts\PaymentTransactionRepositoryInterface::class)->find($id);

        if (!$transaction || $transaction->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Transaction not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $transaction,
        ]);
    }
}
