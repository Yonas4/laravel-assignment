<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Data\Cart\CartItemData;
use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService
    ) {}

    public function index(): JsonResponse
    {
        $cart = $this->cartService->getCartWithItems((string) auth()->id());

        return response()->json([
            'success' => true,
            'data' => $cart,
        ]);
    }

    public function store(CartItemData $request): JsonResponse
    {
        $cart = $this->cartService->addItem((string) auth()->id(), $request);

        return response()->json([
            'success' => true,
            'data' => $cart,
        ], 201);
    }

    public function destroy(string $itemId): JsonResponse
    {
        $this->cartService->removeItem((string) auth()->id(), $itemId);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart.',
        ]);
    }

    public function clear(): JsonResponse
    {
        $this->cartService->clearCart((string) auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared.',
        ]);
    }
}
