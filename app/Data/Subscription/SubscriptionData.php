<?php

declare(strict_types=1);

namespace App\Data\Subscription;

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;

class SubscriptionData extends Data
{
    public function __construct(
        public string $id,
        public SubscriptionPlan $plan,
        public SubscriptionStatus $status,
        public CarbonInterface $starts_at,
        public CarbonInterface $ends_at,
    ) {}
}
