<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Cart\CartItemData;
use App\Repositories\Contracts\PackageRepositoryInterface;
use App\Services\Traits\Loggable;
use Illuminate\Validation\ValidationException;

class PackageService
{
    use Loggable;

    public function __construct(
        private readonly PackageRepositoryInterface $packageRepository,
        private readonly CartService $cartService
    ) {}

    public function addToCart(string $userId, string $packageId): void
    {
        $this->logStart('package_add_to_cart', ['user_id' => $userId, 'package_id' => $packageId]);

        try {
            $package = $this->packageRepository->findById($packageId);
            
            if (!$package) {
                throw ValidationException::withMessages([
                    'package_id' => ['The selected package does not exist.']
                ]);
            }

            foreach ($package->services as $service) {
                $this->cartService->addItem($userId, new CartItemData($service->id, 1));
            }

            $this->logSuccess('package_add_to_cart', ['package_id' => $packageId]);
        } catch (\Throwable $e) {
            $this->logFailure('package_add_to_cart', $e, ['user_id' => $userId, 'package_id' => $packageId]);
            throw $e;
        }
    }
}
