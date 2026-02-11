<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
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

    if ($expected === '' || !hash_equals($expected, $token)) {
        abort(404);
    }

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
 * ROTAS INTERNAS SEGURAS (SPIN) - versão compatível com celular (GET)
 * - não depende de auth()
 * - grava em spin_runs (se existir)
 * - 404 se token errado (stealth)
 *
 * START (GET):
 * /_internal/spin/start?token=XXX&provider=demo&game_code=demo_game
 *
 * STATUS (GET):
 * /_internal/spin/status/{request_id}?token=XXX
 */
Route::get('/_internal/spin/start', function () {
    $token = (string) request()->query('token', '');
    $expected = (string) env('QUEUE_TEST_TOKEN', '');

    if ($expected === '' || !hash_equals($expected, $token)) {
        abort(404);
    }

    $provider  = (string) request()->query('provider', 'demo');
    $game_code = (string) request()->query('game_code', 'demo_game');

    $requestId = (string) Str::uuid();

    // Se a tabela spin_runs existir, grava (se não existir, só retorna o request_id)
    try {
        DB::table('spin_runs')->insert([
            'request_id' => $requestId,
            'provider' => $provider,
            'game_code' => $game_code,
            'status' => 'queued',
            'result' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    } catch (\Throwable $e) {
        Log::warning('SPIN_START: não gravou em spin_runs (tabela/colunas podem não existir)', [
            'request_id' => $requestId,
            'err' => $e->getMessage(),
        ]);
    }

    // Se existir um job de spin, despacha. Se não existir, não quebra deploy.
    if (class_exists(\App\Jobs\ProcessSpinJob::class)) {
        \App\Jobs\ProcessSpinJob::dispatch($requestId)
            ->onConnection('database')
            ->onQueue('default');
    } else {
        Log::info('SPIN_START: ProcessSpinJob não existe, apenas gerando request_id', ['request_id' => $requestId]);
    }

    return response()->json([
        'ok' => true,
        'request_id' => $requestId,
        'status' => 'queued',
        'provider' => $provider,
        'game_code' => $game_code,
        'ts' => now()->toDateTimeString(),
    ]);
});

Route::get('/_internal/spin/status/{request_id}', function (string $request_id) {
    $token = (string) request()->query('token', '');
    $expected = (string) env('QUEUE_TEST_TOKEN', '');

    if ($expected === '' || !hash_equals($expected, $token)) {
        abort(404);
    }

    try {
        $row = DB::table('spin_runs')->where('request_id', $request_id)->first();
    } catch (\Throwable $e) {
        return response()->json(['ok' => false, 'error' => 'spin_runs_unavailable'], 500);
    }

    if (!$row) {
        return response()->json(['ok' => false, 'error' => 'not_found'], 404);
    }

    return response()->json([
        'ok' => true,
        'request_id' => $row->request_id,
        'status' => $row->status ?? null,
        'result' => $row->result ?? null,
        'updated_at' => isset($row->updated_at) ? (string) $row->updated_at : null,
    ]);
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