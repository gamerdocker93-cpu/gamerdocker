<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

/**
 * Healthcheck simples (opcional)
 */
Route::get('/health', function () {
    return response()->json(['ok' => true]);
});

/**
 * TESTE DA FILA (Database Queue)
 * Acesse: /test-queue
 *
 * OBS: Está “safe” para não derrubar deploy caso o Job não exista.
 */
Route::get('/test-queue', function () {
    try {
        $jobClass = \App\Jobs\TestQueueJob::class;

        if (!class_exists($jobClass)) {
            return response()->json([
                'ok' => false,
                'message' => 'Classe TestQueueJob não encontrada em app/Jobs/TestQueueJob.php',
            ], 500);
        }

        dispatch(new $jobClass());

        return response()->json([
            'ok' => true,
            'message' => 'Job enviado para a fila com sucesso!',
            'time' => now()->toDateTimeString(),
        ]);
    } catch (\Throwable $e) {
        Log::error('Erro ao despachar TestQueueJob', [
            'message' => $e->getMessage(),
        ]);

        return response()->json([
            'ok' => false,
            'message' => 'Falha ao enviar job para fila',
            'error' => $e->getMessage(),
        ], 500);
    }
});

/**
 * Carrega as rotas do sistema (se existirem)
 */
if (file_exists(__DIR__ . '/groups/layouts/app.php')) {
    include_once __DIR__ . '/groups/layouts/app.php';
}

/**
 * Vue está em HASH MODE (#/...), então não precisa SPA fallback.
 */
Route::get('/', function () {
    return view('layouts.app');
});
