<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;
use function Pest\Laravel\actingAs;

describe('Trial Subscription', function () {
    it('allows a new user to activate a trial subscription', function () {
        $user = User::factory()->create();
        $trialPlan = SubscriptionPlan::factory()->trial()->create();

        $response = actingAs($user, 'sanctum')->postJson('/api/v1/subscriptions/trial');

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'plan_id',
                    'status',
                    'starts_at',
                    'ends_at',
                ],
            ]);

        assertDatabaseHas('user_subscriptions', [
            'user_id' => $user->id,
            'plan_id' => $trialPlan->id,
            'status' => 'active',
        ]);
    });

    it('prevents a user from activating trial twice', function () {
        $user = User::factory()->create();
        $trialPlan = SubscriptionPlan::factory()->trial()->create();
        
        Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $trialPlan->id,
            'type' => 'trial',
            'status' => 'active',
        ]);

        $response = actingAs($user, 'sanctum')->postJson('/api/v1/subscriptions/trial');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['trial']);
    });
});
