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
            'data'    => $this->formatCart($cart),
        ]);
    }

    public function store(CartItemData $request): JsonResponse
    {
        $cart = $this->cartService->addItem((string) auth()->id(), $request);

        return response()->json([
            'success' => true,
            'data'    => $this->formatCart($cart),
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

    /**
     * Format a cart with computed totals for the response.
     *
     * @param  \App\Models\Cart  $cart
     * @return array<string, mixed>
     */
    private function formatCart(\App\Models\Cart $cart): array
    {
        $items = $cart->items ?? collect();

        return [
            'id'          => $cart->id,
            'user_id'     => $cart->user_id,
            'items'       => $items->values(),
            'total_items' => (int) $items->sum('quantity'),
            'total'       => (float) $items->sum(fn ($i) => $i->unit_price * $i->quantity),
            'created_at'  => $cart->created_at,
            'updated_at'  => $cart->updated_at,
        ];
    }
}
