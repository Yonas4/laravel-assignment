<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Cart\CartItemData;
use App\Models\Package;
use App\Repositories\Contracts\PackageRepositoryInterface;
use App\Services\Traits\Loggable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class PackageService
{
    use Loggable;

    public function __construct(
        private readonly PackageRepositoryInterface $packageRepository,
        private readonly CartService $cartService
    ) {}

    public function getAllPackages(): Collection
    {
        return $this->packageRepository->all();
    }

    public function getPackageById(string $id): ?Package
    {
        return $this->packageRepository->findById($id);
    }

    public function addToCart(string $userId, string $packageId): void
    {
        $this->logStart('package_add_to_cart', ['user_id' => $userId, 'package_id' => $packageId]);

        try {
            $package = $this->packageRepository->findById($packageId);

            if (!$package) {
                throw ValidationException::withMessages([
                    'package_id' => ['The selected package does not exist.'],
                ]);
            }

            // Use eager-loaded services (from repository), no lazy loading
            foreach ($package->services as $service) {
                $this->cartService->addItem($userId, new CartItemData(
                    item_id: $service->id,
                    item_type: 'service',
                    quantity: 1,
                ));
            }

            $this->logSuccess('package_add_to_cart', ['package_id' => $packageId]);
        } catch (\Throwable $e) {
            $this->logFailure('package_add_to_cart', $e, ['user_id' => $userId, 'package_id' => $packageId]);
            throw $e;
        }
    }
}
