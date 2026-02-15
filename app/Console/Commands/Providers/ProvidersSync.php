<?php

namespace App\Console\Commands\Providers;

use Illuminate\Console\Command;
use App\Services\Providers\ProvidersRegistry;
use App\Models\GameProvider;

class ProvidersSync extends Command
{
    protected $signature = 'providers:sync {code?} {--all : Inclui desabilitados também}';
    protected $description = 'Sincroniza providers usando o módulo unificado (DB é fonte de verdade)';

    public function handle(ProvidersRegistry $registry): int
    {
        $code = $this->argument('code');
        $includeDisabled = (bool) $this->option('all');

        // 1) Define lista de códigos (DB é fonte de verdade)
        if ($code) {
            $code = strtolower(trim((string) $code));

            // Se não estiver usando --all, valida enabled no DB antes de resolver
            if (!$includeDisabled) {
                $enabled = GameProvider::query()
                    ->where('code', $code)
                    ->value('enabled');

                if ($enabled === null) {
                    $this->error("ERRO: Provider não existe na tabela game_providers: {$code}");
                    return 0;
                }

                if (!(bool) $enabled) {
                    $this->error("ERRO: Provider '{$code}' está desabilitado. Use --all para incluir.");
                    return 0;
                }
            }

            $codes = [$code];
        } else {
            $q = GameProvider::query()->select('code');

            if (!$includeDisabled) {
                $q->where('enabled', true);
            }

            $codes = $q->pluck('code')
                ->map(fn ($c) => strtolower(trim((string) $c)))
                ->filter(fn ($c) => $c !== '')
                ->unique()
                ->values()
                ->all();
        }

        if (empty($codes)) {
            $this->info('Nenhum provider encontrado no banco para sincronizar.');
            return 0;
        }

        // 2) Resolve e imprime providersList
        foreach ($codes as $c) {
            $this->info(">> Provider: {$c}");

            try {
                $p = $registry->resolve($c);
                $data = $p->providersList();

                $this->line(json_encode([
                    'ok' => true,
                    'code' => $c,
                    'providers_list' => $data,
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            } catch (\Throwable $e) {
                $this->error("ERRO: {$e->getMessage()}");
            }
        }

        return 0;
    }
}