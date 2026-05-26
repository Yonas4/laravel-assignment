<?php

namespace Database\Factories;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionPlan>
 */
class SubscriptionPlanFactory extends Factory
{
    protected $model = SubscriptionPlan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug'          => fake()->unique()->slug(2),
            'name'          => fake()->words(2, true),
            'description'   => fake()->sentence(),
            'price'         => 99.00,
            'currency'      => 'SAR',
            'duration_days' => 30,
            'is_trial'      => false,
            'is_active'     => true,
            'features'      => null,
        ];
    }

    /**
     * Mark plan as a trial plan.
     */
    public function trial(): static
    {
        return $this->state(fn (array $attributes) => [
            'slug'          => 'trial',
            'name'          => 'Free Trial',
            'price'         => 0,
            'duration_days' => 14,
            'is_trial'      => true,
        ]);
    }
}
