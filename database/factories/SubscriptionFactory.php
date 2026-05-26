<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'   => User::factory(),
            'plan_id'   => SubscriptionPlan::factory(),
            'type'      => 'standard',
            'status'    => 'active',
            'starts_at' => now(),
            'ends_at'   => now()->addDays(30),
        ];
    }

    /**
     * State for a trial subscription.
     */
    public function trial(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan_id' => SubscriptionPlan::factory()->trial(),
            'type'    => 'trial',
            'ends_at' => now()->addDays(14),
        ]);
    }
}

