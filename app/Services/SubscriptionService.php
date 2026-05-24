<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Subscription\SubscriptionData;
use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Repositories\Contracts\SubscriptionRepositoryInterface;
use App\Services\Traits\Loggable;
use Illuminate\Validation\ValidationException;

class SubscriptionService
{
    use Loggable;

    public function __construct(
        private readonly SubscriptionRepositoryInterface $subscriptionRepository
    ) {}

    /**
     * Activate a 14-day free trial for a user.
     *
     * @throws ValidationException
     */
    public function activateTrial(string $userId): SubscriptionData
    {
        $this->logStart('activate_trial', ['user_id' => $userId]);

        try {
            // Check if user already has an active subscription
            $activeSubscription = $this->subscriptionRepository->findActiveForUser($userId);
            if ($activeSubscription) {
                throw ValidationException::withMessages([
                    'trial' => ['User already has an active subscription.'],
                ]);
            }

            // Check if user has ever had a trial plan
            if ($this->subscriptionRepository->hasHadPlan($userId, SubscriptionPlan::TRIAL->value)) {
                throw ValidationException::withMessages([
                    'trial' => ['User has already used their trial subscription.'],
                ]);
            }

            // Create the trial subscription
            $subscription = $this->subscriptionRepository->create([
                'user_id' => $userId,
                'plan' => SubscriptionPlan::TRIAL->value,
                'status' => SubscriptionStatus::ACTIVE->value,
                'starts_at' => now(),
                'ends_at' => now()->addDays(14),
            ]);

            $this->logSuccess('activate_trial', ['subscription_id' => $subscription->id]);

            return SubscriptionData::from($subscription);
        } catch (\Throwable $e) {
            $this->logFailure('activate_trial', $e, ['user_id' => $userId]);
            throw $e;
        }
    }
}
