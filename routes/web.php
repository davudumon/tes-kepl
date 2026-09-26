<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

// Landing Page Route
Route::get('/', function () {
    return view('landing');
});

// Task CRUD API Routes
Route::apiResource('tasks', TaskController::class);
