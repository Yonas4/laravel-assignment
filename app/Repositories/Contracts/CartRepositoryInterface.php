<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Cart;
use App\Models\CartItem;

interface CartRepositoryInterface
{
    public function findOrCreateForUser(string $userId): Cart;

    public function addOrIncrementItem(Cart $cart, array $data): CartItem;

    public function removeItem(string $cartItemId): bool;

    public function clear(Cart $cart): bool;

    public function findByUserId(string $userId): ?Cart;
}
