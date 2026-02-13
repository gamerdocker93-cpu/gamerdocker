<?php

namespace App\Console\Commands\Providers;

use Illuminate\Console\Command;
use App\Services\Providers\ProvidersRegistry;

class GamesSync extends Command
{
    protected $signature = 'games:sync {code?}';
    protected $description = 'Sincroniza games do provider usando o módulo unificado (wrapper dos commands existentes)';

    public function handle(ProvidersRegistry $registry): int
    {
        $code = $this->argument('code');

        $codes = $code ? [strtolower($code)] : $registry->supportedCodes();

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
