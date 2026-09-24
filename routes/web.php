<?php

use Illuminate\Support\Facades\Route;

// Landing Page Route
Route::redirect('/', '/projects');

Route::resource('projects', \App\Http\Controllers\ProjectController::class)
    ->only(['index', 'create', 'store', 'destroy']);
