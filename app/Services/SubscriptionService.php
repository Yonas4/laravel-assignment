<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Repositories\Contracts\SubscriptionRepositoryInterface;
use App\Services\Traits\Loggable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class SubscriptionService
{
    use Loggable;

    public function __construct(
        private readonly SubscriptionRepositoryInterface $subscriptionRepository
    ) {}

    /**
     * Get all active subscription plans (public).
     */
    public function getActivePlans(): Collection
    {
        return SubscriptionPlan::where('is_active', true)->get();
    }

    /**
     * Get the current active subscription for a user.
     */
    public function getActiveSubscription(string $userId): ?Subscription
    {
        return $this->subscriptionRepository->findActiveForUser($userId);
    }

    /**
     * Activate a 14-day free trial for a user.
     *
     * @throws ValidationException
     */
    public function activateTrial(string $userId): Subscription
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

            // Find the trial plan
            $trialPlan = SubscriptionPlan::where('is_trial', true)->where('is_active', true)->first();
            if (!$trialPlan) {
                throw ValidationException::withMessages([
                    'trial' => ['Trial plan is not available.'],
                ]);
            }

            // Check if user has ever had a trial
            if ($this->subscriptionRepository->hasHadTrial($userId)) {
                throw ValidationException::withMessages([
                    'trial' => ['User has already used their trial subscription.'],
                ]);
            }

            // Create the trial subscription
            $subscription = $this->subscriptionRepository->create([
                'user_id' => $userId,
                'plan_id' => $trialPlan->id,
                'type' => 'trial',
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => now()->addDays($trialPlan->duration_days),
            ]);

            $this->logSuccess('activate_trial', ['subscription_id' => $subscription->id]);

            return $subscription;
        } catch (\Throwable $e) {
            $this->logFailure('activate_trial', $e, ['user_id' => $userId]);
            throw $e;
        }
    }
}
