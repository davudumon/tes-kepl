<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

// Landing Page Route
Route::get('/', function () {
    return view('landing');
});

// Project CRUD Routes
Route::resource('projects', ProjectController::class)
    ->only(['index', 'create', 'store', 'destroy']);

// Task CRUD API Routes
Route::apiResource('tasks', TaskController::class);
