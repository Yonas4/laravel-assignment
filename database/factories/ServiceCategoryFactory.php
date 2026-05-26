<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceCategory>
 */
class ServiceCategoryFactory extends Factory
{
    protected $model = ServiceCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Plumbing', 'Electrical', 'Cleaning', 'AC Maintenance', 'Painting']),
            'description' => fake()->sentence(),
            'icon' => null,
            'is_active' => true,
        ];
    }
}
