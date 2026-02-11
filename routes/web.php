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
