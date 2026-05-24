<?php

declare(strict_types=1);

use App\Models\User;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;
use function Pest\Laravel\actingAs;

describe('Trial Subscription', function () {
    it('allows a new user to activate a trial subscription', function () {
        $user = User::factory()->create();

        $response = actingAs($user, 'sanctum')->postJson('/api/v1/subscriptions/trial');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'plan',
                    'status',
                    'starts_at',
                    'ends_at',
                ],
            ]);

        assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'plan' => 'trial',
            'status' => 'active',
        ]);
        
        // Assert ends_at is 14 days from now. 
        // Note: the test should just ensure it gets created. We can refine the 14 days check in a unit test.
    });

    it('prevents a user from activating trial twice', function () {
        $user = User::factory()->create();
        
        \App\Models\Subscription::factory()->create([
            'user_id' => $user->id,
            'plan' => 'trial',
            'status' => 'active',
        ]);

        $response = actingAs($user, 'sanctum')->postJson('/api/v1/subscriptions/trial');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['trial']);
    });
});
