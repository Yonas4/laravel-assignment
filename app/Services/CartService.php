<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Cart\CartItemData;
use App\Models\Cart;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Services\Traits\Loggable;
use Illuminate\Validation\ValidationException;

class CartService
{
    use Loggable;

    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly ServiceRepositoryInterface $serviceRepository
    ) {}

    public function getCartWithItems(string $userId): Cart
    {
        $cart = $this->cartRepository->findOrCreateForUser($userId);
        $cart->load('items');

        return $cart;
    }

    public function addItem(string $userId, CartItemData $data): Cart
    {
        $this->logStart('cart_add_item', ['user_id' => $userId, 'item_id' => $data->item_id]);

        try {
            $cart = $this->cartRepository->findOrCreateForUser($userId);

            // Resolve item (service or package service)
            $service = $this->serviceRepository->findById($data->item_id);
            if (!$service || !$service->is_active) {
                throw ValidationException::withMessages([
                    'item_id' => ['The selected service is not available.'],
                ]);
            }

            // Check for existing item — increment quantity instead of creating duplicate
            $this->cartRepository->addOrIncrementItem($cart, [
                'itemable_type' => $service->getMorphClass(),
                'itemable_id' => $service->id,
                'item_type' => $data->item_type,
                'name' => $service->name,
                'quantity' => $data->quantity,
                'unit_price' => $service->price,
            ]);

            $cart->load('items');

            $this->logSuccess('cart_add_item', ['cart_id' => $cart->id]);

            return $cart;
        } catch (\Throwable $e) {
            $this->logFailure('cart_add_item', $e, ['user_id' => $userId]);
            throw $e;
        }
    }

    public function removeItem(string $userId, string $cartItemId): bool
    {
        $cart = $this->cartRepository->findOrCreateForUser($userId);

        $item = $cart->items()->where('id', $cartItemId)->first();
        if (!$item) {
            throw ValidationException::withMessages(['cart_item_id' => ['Item not found in cart.']]);
        }

        return $this->cartRepository->removeItem($cartItemId);
    }

    public function clearCart(string $userId): bool
    {
        $cart = $this->cartRepository->findOrCreateForUser($userId);
        return $this->cartRepository->clear($cart);
    }
}
