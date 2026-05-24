<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Data\Auth\LoginData;
use App\Data\Auth\RegisterData;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function register(RegisterData $data): JsonResponse
    {
        $result = $this->authService->register($data);

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => $result,
        ], 201);
    }

    public function login(LoginData $data): JsonResponse
    {
        $result = $this->authService->login($data);

        return response()->json([
            'success' => true,
            'message' => 'User logged in successfully',
            'data' => $result,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
