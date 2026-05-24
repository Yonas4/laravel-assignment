<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Subscription;

interface SubscriptionRepositoryInterface
{
    public function create(array $data): Subscription;

    public function findActiveForUser(string $userId): ?Subscription;

    public function hasHadTrial(string $userId): bool;
}
