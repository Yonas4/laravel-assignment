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
        $services = $this->serviceRepository->allAvailable();
        return response()->json(['data' => $services]);
    }
}
