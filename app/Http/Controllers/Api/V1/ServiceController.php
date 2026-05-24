<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    public function __construct(
        private readonly ServiceRepositoryInterface $serviceRepository
    ) {}

    public function index(): JsonResponse
    {
        $services = $this->serviceRepository->allActive();

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $service = $this->serviceRepository->findById($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $service,
        ]);
    }
}
