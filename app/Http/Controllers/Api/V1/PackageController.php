<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PackageService;
use Illuminate\Http\JsonResponse;

class PackageController extends Controller
{
    public function __construct(
        private readonly PackageService $packageService
    ) {}

    public function index(): JsonResponse
    {
        $packages = $this->packageService->getAllPackages();

        return response()->json([
            'success' => true,
            'data' => $packages,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $package = $this->packageService->getPackageById($id);

        if (!$package) {
            return response()->json([
                'success' => false,
                'message' => 'Package not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $package,
        ]);
    }

    public function addToCart(string $id): JsonResponse
    {
        $this->packageService->addToCart((string) auth()->id(), $id);

        return response()->json([
            'success' => true,
            'message' => 'Package expanded and added to cart',
        ]);
    }
}
