<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {}

    public function plans(): JsonResponse
    {
        $plans = $this->subscriptionService->getActivePlans();

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    public function trial(Request $request): JsonResponse
    {
        $subscription = $this->subscriptionService->activateTrial((string) $request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $subscription,
        ], 201);
    }

    public function my(Request $request): JsonResponse
    {
        $subscription = $this->subscriptionService->getActiveSubscription((string) $request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $subscription,
        ]);
    }
}
