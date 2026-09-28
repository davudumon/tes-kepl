<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
    ]);
});

/*
|--------------------------------------------------------------------------
| API Routes - tabel CRUD `tasks`
|--------------------------------------------------------------------------
| Prefix otomatis: /api  ->  contoh: GET /api/tasks
| Middleware group `api` (stateless, tanpa session/CSRF) dan sudah
| dilewatkan HandleCors sehingga Vue di laptop (localhost:5173)
| boleh mengganggil endpoint ini.
*/

Route::apiResource('tasks', TaskController::class);
