<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_can_be_persisted(): void
    {
        $project = Project::factory()->create(['name' => 'CI Pipeline']);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'CI Pipeline',
        ]);
    }

    public function test_project_list_is_accessible(): void
    {
        Project::factory()->create(['name' => 'CI Pipeline']);

        $this->get(route('projects.index'))
            ->assertOk()
            ->assertSee('CI Pipeline');
    }

    public function test_project_can_be_created(): void
    {
        $this->post(route('projects.store'), [
            'name' => 'Laravel CRUD',
            'description' => 'Simple one-table application.',
            'status' => 'active',
        ])->assertRedirect(route('projects.index'));

        $this->assertDatabaseHas('projects', [
            'name' => 'Laravel CRUD',
            'status' => 'active',
        ]);
    }
}
