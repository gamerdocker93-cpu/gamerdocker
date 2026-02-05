<?php

use Illuminate\Support\Facades\Route;

/**
 * Healthcheck simples (opcional)
 */
Route::get('/health', function () {
    return response()->json(['ok' => true]);
});

/**
 * Se existir um arquivo routes/auth.php (ex.: Socialite / Breeze / Fortify),
 * carregue aqui para garantir que /auth/* (redirect/callback) funcione.
 */
if (file_exists(__DIR__ . '/auth.php')) {
    require __DIR__ . '/auth.php';
}

/**
 * CARREGA AS ROTAS DO SISTEMA (se existirem)
 * Isso é importante para não “matar” rotas do painel/admin ou outras rotas web.
 */
if (file_exists(__DIR__ . '/groups/layouts/app.php')) {
    include_once(__DIR__ . '/groups/layouts/app.php');
}

/**
 * SPA fallback (Vue Router history mode / hash mode)
 * Qualquer rota que NÃO seja:
 * - /api/*
 * - /admin*
 * - /auth*   (Google OAuth redirect/callback)
 * vai renderizar o Blade do SPA.
 */
Route::get('/{any}', function () {
    return view('layouts.app');
})->where('any', '^(?!api|admin|auth).*$');
