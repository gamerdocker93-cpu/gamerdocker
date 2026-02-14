<?php

namespace App\Console\Commands\Providers;

use Illuminate\Console\Command;
use App\Services\Providers\ProvidersRegistry;
use App\Models\GameProvider;

class GamesSync extends Command
{
    protected $signature = 'games:sync {code?} {--all : Inclui desabilitados também}';
    protected $description = 'Sincroniza games do provider usando o módulo unificado (DB é fonte de verdade)';

    public function handle(ProvidersRegistry $registry): int
    {
        $code = $this->argument('code');
        $includeDisabled = (bool) $this->option('all');

        // Se passar code, roda só ele
        if ($code) {
            $codes = [strtolower(trim((string) $code))];
        } else {
            // Sem code: roda apenas os providers existentes no banco
            $q = GameProvider::query()->select('code');

            if (!$includeDisabled) {
                $q->where('enabled', true);
            }

            $codes = $q->pluck('code')
                ->map(fn ($c) => strtolower(trim((string) $c)))
                ->filter()
                ->values()
                ->all();
        }

        if (empty($codes)) {
            $this->info('Nenhum provider encontrado no banco para sincronizar.');
            return 0;
        }

        foreach ($codes as $c) {
            $this->info(">> Games Sync: {$c}");

            try {
                $p = $registry->resolve($c);
                $data = $p->gamesList();

                $this->line(json_encode([
                    'ok' => true,
                    'code' => $c,
                    'games_list' => $data,
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            } catch (\Throwable $e) {
                $this->error("ERRO: {$e->getMessage()}");
            }
        }

        return 0;
    }
}