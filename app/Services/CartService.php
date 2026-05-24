<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Cart\CartItemData;
use App\Models\Cart;
use App\Models\CartItem;
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

    public function getCartForUser(string $userId): Cart
    {
        return $this->cartRepository->findOrCreateForUser($userId);
    }

    public function addItem(string $userId, CartItemData $data): CartItem
    {
        $this->logStart('cart_add_item', ['user_id' => $userId, 'service_id' => $data->service_id]);

        try {
            $cart = $this->cartRepository->findOrCreateForUser($userId);
            
            $service = $this->serviceRepository->findById($data->service_id);
            if (!$service || !$service->is_available) {
                throw ValidationException::withMessages([
                    'service_id' => ['The selected service is not available.']
                ]);
            }

            $cartItem = $this->cartRepository->addItem($cart, [
                'service_id' => $service->id,
                'quantity' => $data->quantity,
                'price' => $service->price,
            ]);

            $this->logSuccess('cart_add_item', ['cart_item_id' => $cartItem->id]);

            return $cartItem;
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
