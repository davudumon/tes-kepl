<?php

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS)
|--------------------------------------------------------------------------
| Dipakai agar aplikasi Vue di laptop (http://localhost:5173) boleh
| memanggil endpoint JSON di /api/* milik Laravel.
|
| Daftar origin dipisah koma, contoh:
|   CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173
| Kosongkan /wildcard hanya untuk pengembangan lokal.
*/

$origins = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('CORS_ALLOWED_ORIGINS', '*'))
)));

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $origins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
