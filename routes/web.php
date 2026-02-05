// routes/web.php

<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['ok' => true]);
});

if (file_exists(__DIR__ . '/groups/layouts/app.php')) {
    include_once(__DIR__ . '/groups/layouts/app.php');
}

/**
 * SPA fallback
 * NÃO pode capturar /api, /admin e /auth
 */
Route::get('/{any}', function () {
    return view('layouts.app');
})->where('any', '^(?!api|admin|auth).*$');