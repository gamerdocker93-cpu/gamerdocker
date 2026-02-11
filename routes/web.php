<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
 * Alias interno do teste de fila
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
 * =========================
 * ROTAS INTERNAS (SPIN)
 * =========================
 *
 * OBS:
 * - NÃO depende de spin_config
 * - usa tabela existente: spin_runs
 * - gera request_id aqui e grava no banco
 * - retorna JSON (sem Vue)
 */

// helper local: valida token "stealth"
$checkToken = function () {
    $token = (string) request()->query('token', '');
    $expected = (string) env('QUEUE_TEST_TOKEN', '');
    if ($expected === '' || !hash_equals($expected, $token)) {
        abort(404);
    }
};

// GET p/ teste via celular (querystring)
Route::get('/_internal/spin/start', function () use ($checkToken) {
    $checkToken();

    $provider  = (string) request()->query('provider', 'demo');
    $gameCode  = (string) request()->query('game_code', 'demo_game');

    $requestId = (string) Str::uuid();

    // grava em spin_runs (tabela existente)
    DB::table('spin_runs')->insert([
        'request_id' => $requestId,
        'provider'   => $provider,
        'game_code'  => $gameCode,
        'status'     => 'queued',
        'result'     => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Log::info('SPIN_START(GET): queued', [
        'request_id' => $requestId,
        'provider' => $provider,
        'game_code' => $gameCode,
    ]);

    return response()->json([
        'ok' => true,
        'request_id' => $requestId,
        'status' => 'queued',
        'provider' => $provider,
        'game_code' => $gameCode,
        'ts' => now()->toDateTimeString(),
    ]);
});

// POST p/ integração real (body JSON)
Route::post('/_internal/spin/start', function (Request $request) use ($checkToken) {
    $checkToken();

    $provider  = (string) $request->input('provider', 'demo');
    $gameCode  = (string) $request->input('game_code', 'demo_game');

    $requestId = (string) Str::uuid();

    DB::table('spin_runs')->insert([
        'request_id' => $requestId,
        'provider'   => $provider,
        'game_code'  => $gameCode,
        'status'     => 'queued',
        'result'     => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Log::info('SPIN_START(POST): queued', [
        'request_id' => $requestId,
        'provider' => $provider,
        'game_code' => $gameCode,
    ]);

    return response()->json([
        'ok' => true,
        'request_id' => $requestId,
        'status' => 'queued',
    ]);
});

// Consultar status pelo request_id
Route::get('/_internal/spin/status/{request_id}', function (string $request_id) use ($checkToken) {
    $checkToken();

    $row = DB::table('spin_runs')->where('request_id', $request_id)->first();

    if (!$row) {
        return response()->json(['ok' => false, 'error' => 'not_found'], 404);
    }

    return response()->json([
        'ok' => true,
        'request_id' => (string) $row->request_id,
        'status' => (string) $row->status,
        'result' => $row->result,
        'updated_at' => (string) $row->updated_at,
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