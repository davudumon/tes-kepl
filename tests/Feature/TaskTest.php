<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: can list all tasks.
     */
    public function test_can_list_tasks(): void
    {
        Task::factory()->count(3)->create();

        $response = $this->getJson('/tasks');

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    /**
     * Test: can create a task.
     */
    public function test_can_create_task(): void
    {
        $payload = [
            'title' => 'Belajar CI/CD',
            'description' => 'Memahami pipeline build-test-staging-production',
        ];

        $response = $this->postJson('/tasks', $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => 'Belajar CI/CD']);

        $this->assertDatabaseHas('tasks', ['title' => 'Belajar CI/CD']);
    }

    /**
     * Test: can show a single task.
     */
    public function test_can_show_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->getJson("/tasks/{$task->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $task->id]);
    }

    /**
     * Test: can update a task.
     */
    public function test_can_update_task(): void
    {
        $task = Task::factory()->create(['title' => 'Old Title']);

        $response = $this->putJson("/tasks/{$task->id}", [
            'title' => 'New Title',
            'completed' => true,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'New Title']);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'New Title',
            'completed' => true,
        ]);
    }

    /**
     * Test: can delete a task.
     */
    public function test_can_delete_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->deleteJson("/tasks/{$task->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /**
     * Test: creating a task without title fails validation.
     */
    public function test_create_task_requires_title(): void
    {
        $response = $this->postJson('/tasks', [
            'description' => 'No title provided',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('title');
    }
}
