<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Jobs\TestQueueJob;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Spin;
use App\Jobs\ProcessSpinJob;

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
 * ROTAS INTERNAS SEGURAS (SPIN)
 * - não renderiza Vue
 * - 404 se token errado (stealth)
 */

// inicia spin (dispatch pro worker)
Route::post('/_internal/spin/start', function (Request $request) {
    $token = (string) $request->query('token', '');
    $expected = (string) env('QUEUE_TEST_TOKEN', '');

    if ($expected === '' || !hash_equals($expected, $token)) {
        abort(404);
    }

    $requestId = (string) Str::uuid();

    $spin = Spin::create([
        'user_id'    => auth()->id(),
        'provider'   => $request->input('provider'),
        'game_code'  => $request->input('game_code'),
        'status'     => 'queued',
        'request_id' => $requestId,
        'request'    => $request->all(),
    ]);

    // deixa explícito: conexão database + fila default
    ProcessSpinJob::dispatch($requestId)
        ->onConnection('database')
        ->onQueue('default');

    return response()->json([
        'ok' => true,
        'request_id' => $requestId,
        'status' => $spin->status,
    ]);
});

// consulta status/resultado
Route::get('/_internal/spin/{requestId}', function (Request $request, string $requestId) {
    $token = (string) $request->query('token', '');
    $expected = (string) env('QUEUE_TEST_TOKEN', '');

    if ($expected === '' || !hash_equals($expected, $token)) {
        abort(404);
    }

    $spin = Spin::where('request_id', $requestId)->first();
    if (!$spin) {
        return response()->json(['ok' => false, 'error' => 'not_found'], 404);
    }

    return response()->json([
        'ok' => true,
        'status' => $spin->status,
        'result' => $spin->status === 'done' ? $spin->result : null,
        'error' => $spin->status === 'failed' ? $spin->error : null,
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

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// GET temporário p/ teste via celular (token obrigatório)
Route::get('/_internal/spin/start', function () {
    $token = (string) request()->query('token', '');
    $expected = (string) env('QUEUE_TEST_TOKEN', '');

    if ($expected === '' || !hash_equals($expected, $token)) {
        abort(404);
    }

    // parâmetros via querystring (mais fácil no celular)
    $provider  = (string) request()->query('provider', 'demo');
    $game_code = (string) request()->query('game_code', 'demo_game');

    // gera request_id
    $requestId = (string) Str::uuid();

    // Salva numa tabela EXISTENTE do seu projeto: spin_runs
    // (você tem Model SpinRuns.php e provavelmente tabela spin_runs)
    // Se der erro aqui, me mande o schema da tabela spin_runs.
    DB::table('spin_runs')->insert([
        'request_id' => $requestId,
        'provider' => $provider,
        'game_code' => $game_code,
        'status' => 'queued',
        'result' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Despacha job (se você tiver um Job específico, substitui aqui)
    // Por enquanto, vamos só logar para confirmar fluxo
    Log::info('SPIN_START: criado', ['request_id' => $requestId, 'provider' => $provider, 'game_code' => $game_code]);

    return response()->json([
        'ok' => true,
        'request_id' => $requestId,
        'status' => 'queued',
        'provider' => $provider,
        'game_code' => $game_code,
        'ts' => now()->toDateTimeString(),
    ]);
});

// Consultar status pelo request_id
Route::get('/_internal/spin/status/{request_id}', function (string $request_id) {
    $token = (string) request()->query('token', '');
    $expected = (string) env('QUEUE_TEST_TOKEN', '');

    if ($expected === '' || !hash_equals($expected, $token)) {
        abort(404);
    }

    $row = DB::table('spin_runs')->where('request_id', $request_id)->first();

    if (!$row) {
        return response()->json(['ok' => false, 'error' => 'not_found'], 404);
    }

    return response()->json([
        'ok' => true,
        'request_id' => $row->request_id,
        'status' => $row->status,
        'result' => $row->result,
        'updated_at' => (string) $row->updated_at,
    ]);
});