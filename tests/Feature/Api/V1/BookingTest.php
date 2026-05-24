<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Service;
use App\Models\Subscription;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\actingAs;

describe('Services Browsing', function () {
    it('allows anyone to browse services', function () {
        Service::factory()->count(3)->create();

        $response = getJson('/api/v1/services');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name', 'description', 'price']
                ],
            ]);
    });
});

describe('Booking Services', function () {
    it('allows an active subscriber to book a service', function () {
        $user = User::factory()->create();
        Subscription::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'ends_at' => now()->addDays(10),
        ]);

        $service = Service::factory()->create();

        $response = actingAs($user, 'sanctum')->postJson("/api/v1/services/{$service->id}/book", [
            'scheduled_at' => now()->addDays(2)->toDateTimeString(),
            'notes' => 'Please bring cleaning supplies',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'status',
                    'scheduled_at',
                ],
            ]);

        assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'service_id' => $service->id,
            'status' => 'confirmed',
        ]);
    });

    it('prevents non-subscribers from booking a service', function () {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $response = actingAs($user, 'sanctum')->postJson("/api/v1/services/{$service->id}/book", [
            'scheduled_at' => now()->addDays(2)->toDateTimeString(),
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Active subscription required to book a service.',
            ]);
    });
});
