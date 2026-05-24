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

    public function addItem(Cart $cart, array $data): CartItem
    {
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
        return Cart::with('items.service')->where('user_id', $userId)->first();
    }
}
