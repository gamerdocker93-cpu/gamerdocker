<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Jobs\TestQueueJob;
use App\Http\Controllers\HealthController;

/**
 * Healthcheck público (Railway deve usar este)
 */
Route::get('/health', [HealthController::class, 'health']);

/**
 * Healthcheck DB público (pra você testar no navegador)
 */
Route::get('/health/db', [HealthController::class, 'healthDb']);

/**
 * Feature flag: rotas internas só existem se habilitadas
 * Coloque no Railway:
 * INTERNAL_ROUTES_ENABLED=false   (produção)
 * INTERNAL_ROUTES_ENABLED=true    (quando precisar usar)
 */
$internalEnabled = filter_var(env('INTERNAL_ROUTES_ENABLED', false), FILTER_VALIDATE_BOOLEAN);

/**
 * Helper: valida token em modo "stealth" (404 se errado)
 * Token vem de INTERNAL_ROUTES_TOKEN
 */
if (!function_exists('_internalTokenOr404')) {
    function _internalTokenOr404(Request $request): void
    {
        $token = (string) $request->query('token', '');
        $expected = (string) env('INTERNAL_ROUTES_TOKEN', '');

        // Se não configurou token OU token errado -> 404
        if ($expected === '' || !hash_equals($expected, $token)) {
            abort(404);
        }
    }
}

if ($internalEnabled) {

    /**
     * DB PING (interno) - não depende da UI do Railway
     * Use:
     * /_internal/db/ping?token=SEU_TOKEN
     */
    Route::get('/_internal/db/ping', function (Request $request) {
        _internalTokenOr404($request);

        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1 as ok');

            return response()->json([
                'ok' => true,
                'connection' => DB::connection()->getName(),
                'db' => DB::connection()->getDatabaseName(),
                'host' => (string) config('database.connections.mysql.host'),
                'port' => (string) config('database.connections.mysql.port'),
                'ts' => now()->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            Log::error('DB_PING: falhou', ['err' => $e->getMessage()]);

            return response()->json([
                'ok' => false,
                'error' => $e->getMessage(),
                'ts' => now()->toDateTimeString(),
            ], 500);
        }
    });

    /**
     * TESTE DA FILA (não renderiza Vue)
     * /queue-test?token=SEU_TOKEN
     */
    Route::get('/queue-test', function (Request $request) {
        _internalTokenOr404($request);

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
     * Alias interno opcional
     * /_internal/queue/test?token=SEU_TOKEN
     */
    Route::get('/_internal/queue/test', function (Request $request) {
        _internalTokenOr404($request);

        TestQueueJob::dispatch()
            ->onConnection('database')
            ->onQueue('default');

        Log::info('QUEUE_TEST: job despachado via /_internal/queue/test');

        return response()->json([
            'ok' => true,
            'dispatched' => true,
            'ts' => now()->toDateTimeString(),
        ]);
    });

    /**
     * SPIN (modo interno)
     * Usa tabela spin_runs (não depende de Model)
     *
     * Start por GET (fácil no celular):
     * /_internal/spin/start?token=...&provider=demo&game_code=demo_game
     */
    Route::get('/_internal/spin/start', function (Request $request) {
        _internalTokenOr404($request);

        $provider  = (string) $request->query('provider', 'demo');
        $gameCode  = (string) $request->query('game_code', 'demo_game');
        $requestId = (string) Str::uuid();

        try {
            DB::table('spin_runs')->insert([
                'request_id' => $requestId,
                'provider'   => $provider,
                'game_code'  => $gameCode,
                'status'     => 'queued',
                'result'     => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('SPIN_START: falhou insert em spin_runs', [
                'err' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'error' => 'spin_runs_table_missing_or_invalid',
                'detail' => $e->getMessage(),
                'ts' => now()->toDateTimeString(),
            ], 500);
        }

        Log::info('SPIN_START: criado', [
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

    /**
     * Consultar status:
     * /_internal/spin/status/{request_id}?token=...
     */
    Route::get('/_internal/spin/status/{request_id}', function (Request $request, string $request_id) {
        _internalTokenOr404($request);

        try {
            $row = DB::table('spin_runs')->where('request_id', $request_id)->first();
        } catch (\Throwable $e) {
            Log::error('SPIN_STATUS: falhou select em spin_runs', [
                'err' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'error' => 'spin_runs_table_missing_or_invalid',
                'detail' => $e->getMessage(),
                'ts' => now()->toDateTimeString(),
            ], 500);
        }

        if (!$row) {
            return response()->json(['ok' => false, 'error' => 'not_found'], 404);
        }

        return response()->json([
            'ok' => true,
            'request_id' => $row->request_id,
            'status' => $row->status,
            'result' => $row->result,
            'updated_at' => (string) $row->updated_at,
            'ts' => now()->toDateTimeString(),
        ]);
    });
}

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