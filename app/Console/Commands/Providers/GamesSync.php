<?php

namespace App\Console\Commands\Providers;

use App\Models\GameProvider;
use App\Services\Providers\ProvidersRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class GamesSync extends Command
{
    /**
     * - Sem argumento: tenta todos os codes suportados
     * - Com {code}: tenta só aquele
     *
     * Segurança:
     * - Em PRODUÇÃO: não chama gamesList automaticamente (evita 500 sem credenciais / sem contrato)
     * - Em NÃO-PRODUÇÃO: chama gamesList somente se provider estiver enabled
     *
     * Ex:
     * php artisan games:sync
     * php artisan games:sync worldslot
     * php artisan games:sync worldslot --force
     */
    protected $signature = 'games:sync {code?} {--force : Em produção, permite executar gamesList (use com cuidado)}';
    protected $description = 'Sincroniza games do provider usando módulo unificado (seguro em produção)';

    public function handle(ProvidersRegistry $registry): int
    {
        $codeArg = $this->argument('code');
        $codes = $codeArg ? [strtolower((string) $codeArg)] : $registry->supportedCodes();

        $ok = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($codes as $c) {
            $this->info(">> Games Sync: {$c}");

            try {
                // 1) Checa se existe no DB
                $row = GameProvider::query()->where('code', $c)->first();

                if (!$row) {
                    $skipped++;
                    $this->warn("SKIP: provider '{$c}' não existe na tabela game_providers (rode providers:sync).");
                    continue;
                }

                // 2) Checa se está habilitado
                if (!$row->enabled) {
                    $skipped++;
                    $this->warn("SKIP: provider '{$c}' está desabilitado (enabled=false).");
                    continue;
                }

                // 3) Segurança em produção: não rodar sem --force
                $isProd = App::environment('production');
                if ($isProd && !$this->option('force')) {
                    $skipped++;
                    $this->line(json_encode([
                        'ok' => true,
                        'action' => 'production_safe_skip',
                        'code' => $c,
                        'note' => 'Em produção este comando não executa gamesList sem --force.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    continue;
                }

                // 4) Resolve + executa
                $p = $registry->resolve($c);

                $data = $p->gamesList();

                $ok++;
                $this->line(json_encode([
                    'ok' => true,
                    'code' => $c,
                    'count' => is_array($data) ? count($data) : null,
                    'games_list' => $data,
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            } catch (\Throwable $e) {
                $errors++;
                $this->error("ERRO: {$e->getMessage()}");
            }
        }

        $this->info("== DONE == ok={$ok} skipped={$skipped} errors={$errors}");

        return $errors > 0 ? 1 : 0;
    }
}