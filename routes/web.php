<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Jobs\TestQueueJob;

/**
 * Healthcheck simples (opcional)
 */
Route::get('/health', function () {
    return response()->json(['ok' => true]);
});

/**
 * ROTA SEGURA DE TESTE DA FILA (não renderiza Vue)
 * - Se o token estiver errado: 404 (não denuncia que existe)
 * - Se estiver certo: despacha job e retorna JSON
 */
Route::get('/queue-test', function () {
    $token = (string) request()->query('token', '');
    $expected = (string) env('QUEUE_TEST_TOKEN', '');

    // "stealth": se não bater, finge que não existe
    if ($expected === '' || !hash_equals($expected, $token)) {
        abort(404);
    }

    // força usar conexão database (mesmo que env esteja errado)
    TestQueueJob::dispatch()
        ->onConnection('database')
        ->onQueue('default');

    Log::info('QUEUE_TEST: job despachado via /queue-test');

    return response()->json([
        'ok' => true,
        'dispatched' => true,
        'connection' => 'database',
        'queue' => 'default',
        'ts' => now()->toDateTimeString(),
    ]);
});

/**
 * (Opcional) alias interno
 */
Route::get('/_internal/queue/test', function () {
    $token = (string) request()->query('token', '');
    $expected = (string) env('QUEUE_TEST_TOKEN', '');

    if ($expected === '' || !hash_equals($expected, $token)) {
        abort(404);
    }

    TestQueueJob::dispatch()
        ->onConnection('database')
        ->onQueue('default');

    Log::info('QUEUE_TEST: job despachado via /_internal/queue/test');

    return response()->json(['ok' => true, 'dispatched' => true]);
});

/**
 * Carrega as rotas do sistema (se existirem)
 */
if (file_exists(__DIR__ . '/groups/layouts/app.php')) {
    include_once __DIR__ . '/groups/layouts/app.php';
}

/**
 * Página principal (Vue em hash mode)
 */
Route::get('/', function () {
    return view('layouts.app');
});