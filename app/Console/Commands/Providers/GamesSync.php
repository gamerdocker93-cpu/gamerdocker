<?php

namespace App\Console\Commands\Providers;

use Illuminate\Console\Command;
use App\Services\Providers\ProvidersRegistry;
use App\Models\GameProvider;
use Illuminate\Support\Facades\DB;

class GamesSync extends Command
{
    protected $signature = 'games:sync {code?} {--dry-run}';
    protected $description = 'Sincroniza games do provider usando o módulo unificado (salva/atualiza por game_code)';

    public function handle(ProvidersRegistry $registry): int
    {
        $code = $this->argument('code');
        $dryRun = (bool) $this->option('dry-run');

        $codes = $code ? [strtolower((string) $code)] : $registry->supportedCodes();

        foreach ($codes as $c) {
            $c = strtolower(trim((string) $c));
            $this->info(">> Games Sync: {$c}");

            try {
                // 1) Fonte de verdade: DB
                $providerModel = GameProvider::query()->where('code', $c)->first();

                if (!$providerModel) {
                    throw new \InvalidArgumentException("Provider não existe na tabela game_providers: {$c}");
                }

                if (!$providerModel->enabled) {
                    throw new \InvalidArgumentException("Provider '{$c}' está desabilitado.");
                }

                // 2) Resolve implementação (real/fake)
                $p = $registry->resolve($c);

                // 3) provider_id (compat com legado se existir tabela providers)
                $providerIdForGames = (int) $providerModel->id;

                try {
                    if (DB::getSchemaBuilder()->hasTable('providers')) {
                        $legacyId = DB::table('providers')->where('code', $c)->value('id');
                        if ($legacyId) {
                            $providerIdForGames = (int) $legacyId;
                        }
                    }
                } catch (\Throwable $ignored) {
                    // fica com game_providers.id
                }

                // 4) gamesList
                $games = $p->gamesList();

                if (!is_array($games)) {
                    $games = [];
                }

                // Se veio no formato ['games_list' => [...]]
                if (isset($games['games_list']) && is_array($games['games_list'])) {
                    $games = $games['games_list'];
                }

                if ($dryRun) {
                    $this->line(json_encode([
                        'ok' => true,
                        'code' => $c,
                        'dry_run' => true,
                        'count' => count($games),
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                    continue;
                }

                DB::beginTransaction();

                $created = 0;
                $updated = 0;
                $skipped = 0;
                $errors  = 0;

                foreach ($games as $g) {
                    try {
                        if (!is_array($g)) {
                            $skipped++;
                            continue;
                        }

                        $gameCode = (string) ($g['game_code'] ?? '');
                        if ($gameCode === '') {
                            $skipped++;
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
                            $updated++;
                        } else {
                            $created++;
                        }
                    } catch (\Throwable $e) {
                        $errors++;
                        // continua o loop (não derruba o sync inteiro)
                    }
                }

                DB::commit();

                $this->line(json_encode([
                    'ok' => true,
                    'code' => $c,
                    'created' => $created,
                    'updated' => $updated,
                    'skipped' => $skipped,
                    'errors' => $errors,
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            } catch (\Throwable $e) {
                try { DB::rollBack(); } catch (\Throwable $ignored) {}

                $this->error("ERRO: {$e->getMessage()}");
            }
        }

        return 0;
    }
}