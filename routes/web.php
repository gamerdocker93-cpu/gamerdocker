<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;
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

        if ($expected === '' || !hash_equals($expected, $token)) {
            abort(404);
        }
    }
}

if ($internalEnabled) {

    /**
     * Lista os schedules registrados no Laravel (o que o schedule:work vai executar)
     *
     * Use:
     * /_internal/schedule/list?token=SEU_TOKEN
     */
    Route::get('/_internal/schedule/list', function (Request $request) {
        _internalTokenOr404($request);

        try {
            // Preferência: usar Artisan schedule:list (carrega Kernel como no console)
            try {
                $exit = Artisan::call('schedule:list', ['--json' => true]);
                $out  = trim(Artisan::output());

                if ($exit === 0 && $out !== '') {
                    $decoded = json_decode($out, true);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        return response()->json([
                            'ok' => true,
                            'source' => 'artisan:schedule:list --json',
                            'data' => $decoded,
                            'ts' => now()->toDateTimeString(),
                        ]);
                    }

                    return response()->json([
                        'ok' => true,
                        'source' => 'artisan:schedule:list --json (raw)',
                        'raw' => $out,
                        'ts' => now()->toDateTimeString(),
                    ]);
                }
            } catch (\Throwable $ignored) {
                // Se não existir schedule:list nessa versão, cai pro fallback abaixo.
            }

            // Fallback: leitura via Schedule::class (pode vir vazio em alguns setups)
            /** @var Schedule $schedule */
            $schedule = app(Schedule::class);

            $events = collect($schedule->events())->map(function ($event) {
                $expression = null;

                if (property_exists($event, 'expression')) {
                    $expression = $event->expression;
                } elseif (method_exists($event, 'expression')) {
                    $expression = $event->expression();
                }

                return [
                    'description' => (string) ($event->description ?? ''),
                    'expression'  => (string) ($expression ?? ''),
                    'timezone'    => (string) ($event->timezone ?? ''),
                    'command'     => (string) ($event->command ?? ''),
                    'output'      => (string) ($event->output ?? ''),
                    'withoutOverlapping' => (bool) ($event->withoutOverlapping ?? false),
                    'mutexName'   => method_exists($event, 'mutexName') ? (string) $event->mutexName() : '',
                ];
            })->values();

            return response()->json([
                'ok' => true,
                'source' => 'schedule:class (fallback)',
                'count' => $events->count(),
                'events' => $events,
                'ts' => now()->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            Log::error('SCHEDULE_LIST: falhou', ['err' => $e->getMessage()]);

            return response()->json([
                'ok' => false,
                'error' => $e->getMessage(),
                'ts' => now()->toDateTimeString(),
            ], 500);
        }
    });

    /**
     * HEARTBEAT (prova definitiva do schedule rodando)
     *
     * Use:
     * /_internal/schedule/heartbeat?token=SEU_TOKEN
     *
     * Observação: requer tabela scheduler_heartbeats (migration)
     */
    Route::get('/_internal/schedule/heartbeat', function (Request $request) {
        _internalTokenOr404($request);

        $name = (string) $request->query('name', 'process-auto-withdrawal');

        try {
            $row = DB::table('scheduler_heartbeats')->where('name', $name)->first();

            if (!$row) {
                return response()->json([
                    'ok' => true,
                    'exists' => false,
                    'name' => $name,
                    'message' => 'Nenhum heartbeat encontrado ainda (aguarde o schedule rodar).',
                    'ts' => now()->toDateTimeString(),
                ]);
            }

            $lastRanAt = $row->last_ran_at ? \Carbon\Carbon::parse($row->last_ran_at) : null;
            $ageSeconds = $lastRanAt ? now()->diffInSeconds($lastRanAt) : null;

            return response()->json([
                'ok' => true,
                'exists' => true,
                'name' => $row->name,
                'last_ran_at' => (string) $row->last_ran_at,
                'age_seconds' => $ageSeconds,
                'runs' => (int) ($row->runs ?? 0),
                'last_runtime_ms' => (int) ($row->last_runtime_ms ?? 0),
                'last_error' => $row->last_error,
                'ts' => now()->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            Log::error('SCHEDULE_HEARTBEAT: falhou', ['err' => $e->getMessage()]);

            return response()->json([
                'ok' => false,
                'error' => $e->getMessage(),
                'ts' => now()->toDateTimeString(),
            ], 500);
        }
    });

    /**
     * Lista comandos Artisan disponíveis (para auditar)
     *
     * Use:
     * /_internal/artisan/commands?token=SEU_TOKEN
     * /_internal/artisan/commands?token=SEU_TOKEN&q=fivers
     * /_internal/artisan/commands?token=SEU_TOKEN&q=games
     */
    Route::get('/_internal/artisan/commands', function (Request $request) {
        _internalTokenOr404($request);

        $q = mb_strtolower(trim((string) $request->query('q', '')));

        try {
            $kernel = app(\Illuminate\Contracts\Console\Kernel::class);
            $kernel->bootstrap();

            $all = collect(Artisan::all())->keys()->values();

            if ($q !== '') {
                $all = $all->filter(fn ($name) => str_contains(mb_strtolower((string) $name), $q))->values();
            }

            return response()->json([
                'ok' => true,
                'filter' => $q,
                'count' => $all->count(),
                'commands' => $all,
                'ts' => now()->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            Log::error('ARTISAN_COMMANDS_LIST: falhou', ['err' => $e->getMessage()]);

            return response()->json([
                'ok' => false,
                'error' => $e->getMessage(),
                'ts' => now()->toDateTimeString(),
            ], 500);
        }
    });

    /**
     * DB PING (interno)
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
     * Alias interno fila
     * /_internal/queue/test?token=SEU_TOKEN
     */
    Route::get('/_internal/queue/test', function (Request $request) {
        _internalTokenOr404($request);

        TestQueueJob::dispatch()
            ->onConnection('database')
            ->onQueue('default');

        return response()->json([
            'ok' => true,
            'dispatched' => true,
            'ts' => now()->toDateTimeString(),
        ]);
    });

    /**
     * RODAR SYNC DE PROVIDERS/GAMES (interno, sem shell)
     *
     * Use:
     * /_internal/providers/run-sync/testproviderfake?token=SEU_TOKEN
     * /_internal/providers/run-sync/testprovider?token=SEU_TOKEN
     *
     * Obs: isso executa providers:sync {code} e games:sync {code}
     */
    Route::get('/_internal/providers/run-sync/{code}', function (Request $request, string $code) {
        _internalTokenOr404($request);

        $code = strtolower(trim($code));

        try {
            $exit1 = Artisan::call('providers:sync', ['code' => $code]);
            $out1  = trim(Artisan::output());

            $exit2 = Artisan::call('games:sync', ['code' => $code]);
            $out2  = trim(Artisan::output());

            return response()->json([
                'ok' => true,
                'code' => $code,
                'providers_sync' => [
                    'exit' => $exit1,
                    'output' => $out1,
                ],
                'games_sync' => [
                    'exit' => $exit2,
                    'output' => $out2,
                ],
                'ts' => now()->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            Log::error('RUN_SYNC: falhou', [
                'code' => $code,
                'err' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'code' => $code,
                'error' => $e->getMessage(),
                'ts' => now()->toDateTimeString(),
            ], 500);
        }
    });

    /**
     * RUN COMMAND (interno, sem shell) - versão segura
     *
     * Use:
     * /_internal/run-command/providers:sync/testproviderfake?token=9fCq3VwT7mK1xN4pR8sY2hJ6aZ0dQ5uE9bL3nG7tH1kP6vX8cM4yS2rW0zA5eD9fCq3VwT7mK1xN4pR8sY2hJ6aZ0dQ5uE9bL3nG7tH1kP6vX8cM4yS2rW0zA5eD
     * /_internal/run-command/games:sync/testproviderfake?token=9fCq3VwT7mK1xN4pR8sY2hJ6aZ0dQ5uE9bL3nG7tH1kP6vX8cM4yS2rW0zA5eD9fCq3VwT7mK1xN4pR8sY2hJ6aZ0dQ5uE9bL3nG7tH1kP6vX8cM4yS2rW0zA5eD
     * /_internal/run-command/games:sync/testproviderfake?token=9fCq3VwT7mK1xN4pR8sY2hJ6aZ0dQ5uE9bL3nG7tH1kP6vX8cM4yS2rW0zA5eD9fCq3VwT7mK1xN4pR8sY2hJ6aZ0dQ5uE9bL3nG7tH1kP6vX8cM4yS2rW0zA5eD&dry_run=1
     *
     * Observação:
     * - Só permite comandos em allowlist
     * - Só permite "code" alfanumérico + _ + -
     */
    Route::get('/_internal/run-command/{cmd}/{code?}', function (Request $request, string $cmd, ?string $code = null) {
        _internalTokenOr404($request);

        $cmd = trim($cmd);
        $code = $code !== null ? strtolower(trim($code)) : null;

        $allowed = [
            'providers:sync',
            'games:sync',
        ];

        if (!in_array($cmd, $allowed, true)) {
            abort(404);
        }

        if ($code !== null && $code !== '') {
            if (!preg_match('/^[a-z0-9_-]{1,50}$/', $code)) {
                return response()->json([
                    'ok' => false,
                    'error' => 'invalid_code',
                    'ts' => now()->toDateTimeString(),
                ], 422);
            }
        } else {
            $code = null;
        }

        $args = [];
        if ($code !== null) {
            $args['code'] = $code;
        }

        // dry-run só para games:sync
        if ($cmd === 'games:sync' && filter_var($request->query('dry_run', false), FILTER_VALIDATE_BOOLEAN)) {
            $args['--dry-run'] = true;
        }

        try {
            $exit = Artisan::call($cmd, $args);
            $out  = trim(Artisan::output());

            return response()->json([
                'ok' => $exit === 0,
                'cmd' => $cmd,
                'code' => $code,
                'args' => $args,
                'exit' => $exit,
                'output' => $out,
                'ts' => now()->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            Log::error('RUN_COMMAND: falhou', [
                'cmd' => $cmd,
                'code' => $code,
                'err' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'cmd' => $cmd,
                'code' => $code,
                'error' => $e->getMessage(),
                'ts' => now()->toDateTimeString(),
            ], 500);
        }
    });

    /**
     * SPIN interno
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
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage(),
                'ts' => now()->toDateTimeString(),
            ], 500);
        }

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
     * /_internal/spin/status/{request_id}?token=...
     */
    Route::get('/_internal/spin/status/{request_id}', function (Request $request, string $request_id) {
        _internalTokenOr404($request);

        try {
            $row = DB::table('spin_runs')->where('request_id', $request_id)->first();
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage(),
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
 * Carrega rotas do sistema
 */
if (file_exists(__DIR__ . '/groups/layouts/app.php')) {
    include_once __DIR__ . '/groups/layouts/app.php';
}

/**
 * Página principal (Vue hash mode)
 */
Route::get('/', function () {
    return view('layouts.app');
});