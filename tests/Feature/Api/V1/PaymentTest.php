<?php

declare(strict_types=1);

use App\Models\User;
use function Pest\Laravel\assertDatabaseHas;

describe('Payment Gateways Availability', function () {
    it('returns available payment gateways for a specific city and module', function () {
        $response = apiGet('/payments/gateways?city=Riyadh&module=booking');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['key', 'label']
                ]
            ]);
            
        $gateways = collect($response->json('data'))->pluck('key');
        
        expect($gateways)->toContain('moyasar', 'tap');
    });

    it('requires city and module parameters', function () {
        $response = apiGet('/payments/gateways');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['city', 'module']);
    });
});

describe('Payment Initiation', function () {
    it('initiates a payment successfully', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = apiPost('/payments/initiate', [
            'gateway' => 'moyasar',
            'module' => 'booking',
            'amount' => 150.00,
            'currency' => 'SAR',
            'city' => 'Riyadh',
            'idempotency_key' => (string) \Illuminate\Support\Str::uuid(),
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'transaction_id',
                    'gateway',
                    'amount',
                    'currency',
                    'status',
                    'redirect_url'
                ]
            ]);

        assertDatabaseHas('payment_transactions', [
            'user_id' => $user->id,
            'gateway' => 'moyasar',
            'module' => 'booking',
            'status' => 'pending',
            'amount' => 150.00,
        ]);
    });
});

describe('Payment Callback', function () {
    it('processes a payment callback successfully and idempotently', function () {
        $transaction = \App\Models\PaymentTransaction::factory()->create([
            'gateway' => 'moyasar',
            'status' => 'pending',
            'gateway_transaction_id' => 'test_txn_123'
        ]);

        $payload = [
            'id' => 'test_txn_123',
            'status' => 'paid',
            'amount' => 15000,
            'message' => 'APPROVED'
        ];

        // First callback
        $response = apiPost('/payments/callback/moyasar', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        assertDatabaseHas('payment_transactions', [
            'id' => $transaction->id,
            'status' => 'success',
        ]);
        
        $transaction->refresh();
        $this->assertNotNull($transaction->paid_at);

        // Second callback (idempotent)
        $response2 = apiPost('/payments/callback/moyasar', $payload);
        $response2->assertStatus(200);
        
        $this->assertEquals($transaction->paid_at->timestamp, $transaction->fresh()->paid_at->timestamp);
    });
});
