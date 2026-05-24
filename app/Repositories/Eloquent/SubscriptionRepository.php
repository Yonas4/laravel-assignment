<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Subscription;
use App\Repositories\Contracts\SubscriptionRepositoryInterface;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function create(array $data): Subscription
    {
        return Subscription::create($data);
    }

    public function findActiveForUser(string $userId): ?Subscription
    {
        return Subscription::where('user_id', $userId)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->first();
    }
    
    public function hasHadPlan(string $userId, string $plan): bool
    {
        return Subscription::withTrashed()
            ->where('user_id', $userId)
            ->where('plan', $plan)
            ->exists();
    }
}
