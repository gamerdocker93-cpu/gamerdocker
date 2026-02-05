<?php

use Illuminate\Support\Facades\Route;

/**
 * Healthcheck simples (opcional)
 */
Route::get('/health', function () {
    return response()->json(['ok' => true]);
});

/**
 * Carrega as rotas do sistema (se existirem)
 * IMPORTANTE: isso mantém rotas do painel/admin e outras rotas web.
 */
if (file_exists(__DIR__ . '/groups/layouts/app.php')) {
    include_once __DIR__ . '/groups/layouts/app.php';
}

/**
 * IMPORTANTE:
 * Como o seu Vue está em HASH MODE (#/...), não precisamos de SPA fallback aqui.
 * O Laravel só precisa servir a página principal ("/") e as rotas web reais.
 */
Route::get('/', function () {
    return view('layouts.app');
});