<?php

namespace Database\Factories;

use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentTransaction>
 */
use App\Models\User;

class PaymentTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'               => User::factory(),
            'gateway'               => 'moyasar',
            'module'                => 'booking',
            'amount'                => 150.00,
            'currency'              => 'SAR',
            'status'                => 'pending',
            'idempotency_key'       => fake()->uuid(),
            'gateway_transaction_id'=> null,
        ];
    }
}
