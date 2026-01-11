<?php

use App\Models\Task;
use App\Models\User;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\deleteJson;

it('allows authenticated user to create a task', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $token = $user->createToken('test-token')->plainTextToken;

    postJson('/api/tasks', [
        'statement' => 'Task 1',
        'task_date' => '2024-01-08',
    ], [
        'Authorization' => 'Bearer ' . $token,
    ])
        ->assertStatus(201)
        ->assertJsonStructure([
            'data' => ['id', 'statement', 'is_completed', 'task_date', 'sort_order', 'created_at', 'updated_at'],
        ])
        ->assertJson([
            'data' => [
                'statement' => 'Task 1',
                'task_date' => '2024-01-08',
            ],
        ]);
});

it('rejects task creation without authentication', function () {
    postJson('/api/tasks', [
        'statement' => 'Task 1',
        'task_date' => '2025-01-08',
    ])
        ->assertStatus(401);
});

it('validates required fields when creating task', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);
    $token = $user->createToken('test-token')->plainTextToken;

    postJson('/api/tasks', [], [
        'Authorization' => 'Bearer ' . $token,
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['statement', 'task_date']);
});

it('allows authenticated user to get all tasks', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);
    $token = $user->createToken('test-token')->plainTextToken;

    Task::factory()->create([
        'user_id' => $user->id,
        'statement' => 'Task 1',
        'task_date' => '2025-01-08',
    ]);

    Task::factory()->create([
        'user_id' => $user->id,
        'statement' => 'Task 2',
        'task_date' => '2025-01-09',
    ]);

    getJson('/api/tasks', [
        'Authorization' => 'Bearer ' . $token,
    ])
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'statement', 'task_date', 'is_completed'],
            ],
        ])
        ->assertJsonCount(2, 'data');
});

it('allows filtering tasks by date', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);
    $token = $user->createToken('test-token')->plainTextToken;

    Task::factory()->create([
        'user_id' => $user->id,
        'statement' => 'Task 1',
        'task_date' => '2025-01-08',
    ]);
    Task::factory()->create([
        'user_id' => $user->id,
        'statement' => 'Task 2',
        'task_date' => '2025-01-09',
    ]);

    getJson('/api/tasks?date=2025-01-08', [
        'Authorization' => 'Bearer ' . $token,
    ])
        ->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

it('allows authenticated user to view single task', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);
    $token = $user->createToken('test-token')->plainTextToken;

    $task = Task::factory()->create([
        'user_id' => $user->id,
        'statement' => 'Task 1',
        'task_date' => '2025-01-08',
    ]);

    getJson("/api/tasks/{$task->id}", [
        'Authorization' => 'Bearer ' . $token,
    ])
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $task->id,
                'statement' => 'Task 1',
            ],
        ]);
});

it('prevents user from viewing another users task', function () {
    $user1 = User::factory()->create([
        'email' => 'user1@example.com',
    ]);
    $user2 = User::factory()->create([
        'email' => 'user2@example.com',
    ]);

    $token = $user1->createToken('test-token')->plainTextToken;

    $task = Task::factory()->create([
        'user_id' => $user2->id,
        'statement' => 'User 2 task',
    ]);

    // User1 tries to view user2's task
    getJson("/api/tasks/{$task->id}", [
        'Authorization' => 'Bearer ' . $token,
    ])
        ->assertStatus(403);
});

it('allows authenticated user to update their task', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);
    $token = $user->createToken('test-token')->plainTextToken;

    $task = Task::factory()->create([
        'user_id' => $user->id,
        'statement' => 'Task 1',
        'is_completed' => false,
    ]);

    patchJson("/api/tasks/{$task->id}", [
        'statement' => 'Task 1 updated',
        'is_completed' => true,
    ], [
        'Authorization' => 'Bearer ' . $token,
    ])
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                'statement' => 'Task 1 updated',
                'is_completed' => true,
            ],
        ]);
});

it('prevents user from updating another users task', function () {
    $user1 = User::factory()->create([
        'email' => 'user1@example.com'
    ]);
    $user2 = User::factory()->create([
        'email' => 'user2@example.com'
    ]);

    $token = $user1->createToken('test-token')->plainTextToken;

    $task = Task::factory()->create([
        'user_id' => $user2->id,
        'statement' => 'User 2 task',
    ]);

    // User1 tries to update user2's task
    patchJson("/api/tasks/{$task->id}", [
        'statement' => 'Update user 2 task',
    ], [
        'Authorization' => 'Bearer ' . $token,
    ])
        ->assertStatus(403);
});

it('allows authenticated user to delete their task', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);
    $token = $user->createToken('test-token')->plainTextToken;

    $task = Task::factory()->create([
        'user_id' => $user->id,
        'statement' => 'Task 1',
    ]);

    deleteJson("/api/tasks/{$task->id}", [], [
        'Authorization' => 'Bearer ' . $token,
    ])
        ->assertStatus(200);

    // Verify that task is deleted from database
    expect(Task::find($task->id))->toBeNull();
});

it('allows authenticated user to search tasks', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);
    $token = $user->createToken('test-token')->plainTextToken;

    Task::factory()->create([
        'user_id' => $user->id,
        'statement' => 'Task 1',
    ]);
    Task::factory()->create([
        'user_id' => $user->id,
        'statement' => 'Task 2',
    ]);

    getJson('/api/tasks/search?keyword=Task 1', [
        'Authorization' => 'Bearer ' . $token,
    ])
        ->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

it('allows authenticated user to reorder tasks', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);
    $token = $user->createToken('test-token')->plainTextToken;

    $task1 = Task::factory()->create([
        'user_id' => $user->id,
        'statement' => 'Task 1',
        'sort_order' => 0,
    ]);
    $task2 = Task::factory()->create([
        'user_id' => $user->id,
        'statement' => 'Task 2',
        'sort_order' => 1,
    ]);

    // Reorder tasks positions
    patchJson('/api/tasks/reorder', [
        'tasks' => [
            ['id' => $task2->id, 'sort_order' => 0],
            ['id' => $task1->id, 'sort_order' => 1],
        ],
    ], [
        'Authorization' => 'Bearer ' . $token,
    ])
        ->assertStatus(200);

    // Verify that ordering is changed in database
    expect($task1->fresh()->sort_order)->toBe(1);
    expect($task2->fresh()->sort_order)->toBe(0);
});
