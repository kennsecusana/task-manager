<?php

use App\Models\User;

use function Pest\Laravel\postJson;
use function Pest\Laravel\getJson;

it('allow user login with valid credentials', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ])
        ->assertStatus(200)
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
            'token',
        ]);
});

it('rejects user login with invalid credentials', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ])
        ->assertStatus(422);
});

it('rejects user login with missing email', function () {
    postJson('/api/login', [
        'password' => 'password123',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('rejects user login with missing password', function () {
    postJson('/api/login', [
        'email' => 'test@example.com',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('allows authenticated user to logout', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $token = $user->createToken('test-token')->plainTextToken;

    postJson('/api/logout', [], [
        'Authorization' => 'Bearer ' . $token,
    ])
        ->assertStatus(200);
});

it('rejects logout without authentication', function () {
    postJson('/api/logout')
        ->assertStatus(401);
});

it('returns authenticated user data', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $token = $user->createToken('test-token')->plainTextToken;

    getJson('/api/user', [
        'Authorization' => 'Bearer ' . $token,
    ])
        ->assertStatus(200)
        ->assertJson([
            'id' => $user->id,
            'email' => $user->email,
        ]);
});

it('rejects user endpoint without authentication', function () {
    getJson('/api/user')
        ->assertStatus(401);
});
