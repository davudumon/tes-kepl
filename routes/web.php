<?php

use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

// Landing Page Route
Route::get('/', function () {
    return view('landing');
});

// Project CRUD Routes
Route::resource('projects', ProjectController::class)
    ->only(['index', 'create', 'store', 'destroy']);

// Catatan: route JSON untuk tabel `tasks` sudah pindah ke routes/api.php
// sehingga tersedia di /api/tasks (middleware group `api` + CORS).
