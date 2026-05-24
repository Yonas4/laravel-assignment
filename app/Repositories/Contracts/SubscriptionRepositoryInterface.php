<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Subscription;

interface SubscriptionRepositoryInterface
{
    /**
     * Create a new subscription.
     */
    public function create(array $data): Subscription;

    /**
     * Find an active subscription for a given user.
     */
    public function findActiveForUser(string $userId): ?Subscription;
    
    /**
     * Check if a user has ever had a specific plan (e.g., trial).
     */
    public function hasHadPlan(string $userId, string $plan): bool;
}
