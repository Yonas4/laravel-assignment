<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\PackageRepositoryInterface;
use App\Services\PackageService;
use Illuminate\Http\JsonResponse;

class PackageController extends Controller
{
    public function __construct(
        private readonly PackageRepositoryInterface $packageRepository,
        private readonly PackageService $packageService
    ) {}

    public function index(): JsonResponse
    {
        $packages = $this->packageRepository->all();
        return response()->json(['data' => $packages]);
    }

    public function addToCart(string $id): JsonResponse
    {
        $this->packageService->addToCart((string) auth()->id(), $id);
        return response()->json([
            'success' => true,
            'message' => 'Package expanded and added to cart'
        ], 200);
    }
}
