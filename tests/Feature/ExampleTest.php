<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_project_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('projects.index'));
        $this->assertTrue(Route::has('projects.store'));
    }
}
