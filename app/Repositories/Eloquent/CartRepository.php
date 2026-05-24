<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Cart;
use App\Models\CartItem;
use App\Repositories\Contracts\CartRepositoryInterface;

class CartRepository implements CartRepositoryInterface
{
    public function findOrCreateForUser(string $userId): Cart
    {
        return Cart::firstOrCreate(['user_id' => $userId]);
    }

    /**
     * Add item to cart or increment quantity if already exists.
     */
    public function addOrIncrementItem(Cart $cart, array $data): CartItem
    {
        $existing = $cart->items()
            ->where('itemable_type', $data['itemable_type'])
            ->where('itemable_id', $data['itemable_id'])
            ->first();

        if ($existing) {
            $existing->increment('quantity', $data['quantity']);
            return $existing->fresh();
        }

        return $cart->items()->create($data);
    }

    public function removeItem(string $cartItemId): bool
    {
        return CartItem::where('id', $cartItemId)->delete() > 0;
    }

    public function clear(Cart $cart): bool
    {
        return $cart->items()->delete() > 0;
    }

    public function findByUserId(string $userId): ?Cart
    {
        return Cart::with('items')->where('user_id', $userId)->first();
    }
}
