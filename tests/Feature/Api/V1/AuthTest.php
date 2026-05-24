<?php

declare(strict_types=1);

use App\Models\User;

describe('Authentication', function () {
    it('registers a new user successfully', function () {
        $response = apiPost('/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    });

    it('logs in an existing user successfully', function () {
        $user = User::factory()->create([
            'email' => 'jane@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = apiPost('/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                ]
            ]);
    });

    it('fails to login with invalid credentials', function () {
        $user = User::factory()->create([
            'email' => 'jack@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = apiPost('/auth/login', [
            'email' => 'jack@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('logs out an authenticated user', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = apiPost('/auth/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);

        $this->assertCount(0, $user->tokens);
    });
});
