<?php

use Illuminate\Support\Facades\Route;

/**
 * Healthcheck simples (opcional)
 */
Route::get('/health', function () {
    return response()->json(['ok' => true]);
});

/**
 * CARREGA AS ROTAS DO SISTEMA (se existirem)
 * Isso é importante para não “matar” rotas do painel/admin ou outras rotas web.
 */
if (file_exists(__DIR__ . '/groups/layouts/app.php')) {
    include_once(__DIR__ . '/groups/layouts/app.php');
}

/**
 * SPA fallback (Vue Router history mode)
 * Qualquer rota que NÃO seja:
 * - /api/*
 * - /admin*
 * vai renderizar o Blade do SPA.
 */
Route::get('/{any}', function () {
    return view('layouts.app');
})->where('any', '^(?!api|admin).*$');