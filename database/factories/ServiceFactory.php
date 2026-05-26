<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id'      => ServiceCategory::factory(),
            'name'             => fake()->words(3, true),
            'description'      => fake()->sentence(),
            'price'            => fake()->randomFloat(2, 20, 500),
            'currency'         => 'SAR',
            'duration_minutes' => fake()->randomElement([30, 60, 90, 120]),
            'is_active'        => true,
        ];
    }
}
