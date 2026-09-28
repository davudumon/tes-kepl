<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bukti requirement: endpoint JSON dari tabel CRUD `tasks` tersedia di
 * /api/tasks dan boleh diakses aplikasi Vue di laptop (localhost:5173).
 */
class ApiCorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_api_tasks_returns_json_list(): void
    {
        Task::factory()->count(2)->create(['completed' => true]);

        $response = $this->getJson('/api/tasks');

        $response->assertOk()
            ->assertJsonCount(2)
            ->assertJsonStructure([
                ['id', 'title', 'description', 'completed', 'created_at', 'updated_at'],
            ]);
    }

    public function test_api_tasks_is_cors_enabled_for_the_vue_dev_server(): void
    {
        Task::factory()->create();

        $response = $this->withHeaders([
            'Origin' => 'http://localhost:5173',
        ])->getJson('/api/tasks');

        $response->assertOk();
        $this->assertTrue(
            $response->headers->has('Access-Control-Allow-Origin'),
            'Header Access-Control-Allow-Origin tidak ada; periksa config/cors.php.'
        );
    }

    public function test_api_tasks_accepts_cors_preflight_from_the_vue_dev_server(): void
    {
        $response = $this->withServerVariables([
            'HTTP_ORIGIN' => 'http://localhost:5173',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ])->call('OPTIONS', '/api/tasks');

        $this->assertTrue(
            $response->headers->has('Access-Control-Allow-Methods'),
            'Preflight CORS ditolak; periksa config/cors.php dan middleware HandleCors.'
        );
    }

    public function test_api_health_endpoint_is_reachable(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok');
    }
}
