<?php

namespace App\Console\Commands\Providers;

use Illuminate\Console\Command;
use App\Services\Providers\ProvidersRegistry;
use Illuminate\Support\Facades\DB;

class GamesSync extends Command
{
    protected $signature = 'games:sync {code?} {--dry-run}';
    protected $description = 'Sincroniza games do provider usando o módulo unificado (salva/atualiza por game_code)';

    public function handle(ProvidersRegistry $registry): int
    {
        $code = $this->argument('code');
        $dryRun = (bool) $this->option('dry-run');

        $codes = $code ? [strtolower($code)] : $registry->supportedCodes();

        $summary = [
            'ok' => true,
            'dry_run' => $dryRun,
            'providers' => [],
            'ts' => now()->toDateTimeString(),
        ];

        foreach ($codes as $c) {
            $this->info(">> Games Sync: {$c}");

            $providerSummary = [
                'code' => $c,
                'ok' => true,
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => 0,
                'error' => null,
            ];

            try {
                $p = $registry->resolve($c);            // ProviderInterface
                $providerModel = $p->provider();        // GameProvider (esperado no seu setup)

                // FK legado: games.provider_id -> providers.id (se existir)
                $legacyProviderId = null;
                try {
                    if (DB::getSchemaBuilder()->hasTable('providers')) {
                        $legacyProviderId = DB::table('providers')
                            ->where('code', $c)
                            ->value('id');
                    }
                } catch (\Throwable $ignored) {
                    // se der qualquer erro aqui, cai pro id do GameProvider
                    $legacyProviderId = null;
                }

                $providerIdForGames = $legacyProviderId ?: $providerModel->id;

                $games = $p->gamesList();

                if (!is_array($games)) {
                    $games = [];
                }

                // Se veio no formato ['games_list' => [...]]
                if (isset($games['games_list']) && is_array($games['games_list'])) {
                    $games = $games['games_list'];
                }

                if ($dryRun) {
                    $providerSummary['skipped'] = count($games);
                    $summary['providers'][] = $providerSummary;

                    $this->line(json_encode([
                        'ok' => true,
                        'code' => $c,
                        'dry_run' => true,
                        'count' => count($games),
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                    continue;
                }

                DB::beginTransaction();

                foreach ($games as $g) {
                    try {
                        if (!is_array($g)) {
                            $providerSummary['skipped']++;
                            continue;
                        }

                        $gameCode = (string) ($g['game_code'] ?? '');
                        if ($gameCode === '') {
                            $providerSummary['skipped']++;
                            continue;
                        }

                        $exists = DB::table('games')->where('game_code', $gameCode)->exists();

                        $payload = [
                            'provider_id'     => $providerIdForGames,
                            'game_server_url' => (string) ($g['game_server_url'] ?? ($providerModel->baseUrl() ?: '')),
                            'game_id'         => (string) ($g['game_id'] ?? ''),
                            'game_name'       => (string) ($g['game_name'] ?? ''),
                            'game_code'       => $gameCode,
                            'game_type'       => (string) ($g['game_type'] ?? 'slot'),
                            'description'     => (string) ($g['description'] ?? 'Jogo DEMO para teste do sistema.'),
                            'cover'           => (string) ($g['cover'] ?? 'https://via.placeholder.com/512x512.png?text=DEMO'),
                            'status'          => (int) ($g['status'] ?? 1),
                            'technology'      => (string) ($g['technology'] ?? 'html5'),
                            'has_lobby'       => (int) ((bool) ($g['has_lobby'] ?? false)),
                            'is_mobile'       => (int) ((bool) ($g['is_mobile'] ?? true)),
                            'has_freespins'   => (int) ((bool) ($g['has_freespins'] ?? false)),
                            'has_tables'      => (int) ((bool) ($g['has_tables'] ?? false)),
                            'only_demo'       => (int) ((bool) ($g['only_demo'] ?? true)),
                            'rtp'             => isset($g['rtp']) ? (float) $g['rtp'] : null,
                            'distribution'    => (string) ($g['distribution'] ?? 'demo'),
                            'views'           => (int) ($g['views'] ?? 0),
                            'is_featured'     => (int) ((bool) ($g['is_featured'] ?? false)),
                            'show_home'       => (int) ((bool) ($g['show_home'] ?? true)),
                            'updated_at'      => now(),
                        ];

                        if (!$exists) {
                            $payload['created_at'] = now();
                        }

                        DB::table('games')->updateOrInsert(
                            ['game_code' => $gameCode],
                            $payload
                        );

                        if ($exists) {
                            $providerSummary['updated']++;
                        } else {
                            $providerSummary['created']++;
                        }
                    } catch (\Throwable $e) {
                        $providerSummary['errors']++;
                        // continua, não derruba o sync inteiro
                    }
                }

                DB::commit();

                $summary['providers'][] = $providerSummary;

                $this->line(json_encode([
                    'ok' => true,
                    'code' => $c,
                    'created' => $providerSummary['created'],
                    'updated' => $providerSummary['updated'],
                    'skipped' => $providerSummary['skipped'],
                    'errors' => $providerSummary['errors'],
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            } catch (\Throwable $e) {
                DB::rollBack();

                $providerSummary['ok'] = false;
                $providerSummary['error'] = $e->getMessage();
                $summary['providers'][] = $providerSummary;

                $this->error("ERRO: {$e->getMessage()}");
            }
        }

        return 0;
    }
}