<?php

namespace App\Console\Commands\Providers;

use Illuminate\Console\Command;
use App\Services\Providers\ProvidersRegistry;

class ProvidersSync extends Command
{
    protected $signature = 'providers:sync {code?}';
    protected $description = 'Sincroniza providers (lista/validação) usando o módulo unificado';

    public function handle(ProvidersRegistry $registry): int
    {
        $code = $this->argument('code');

        $codes = $code ? [strtolower($code)] : $registry->supportedCodes();

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
