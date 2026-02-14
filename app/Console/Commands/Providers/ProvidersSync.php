<?php

namespace App\Console\Commands\Providers;

use App\Models\GameProvider;
use App\Services\Providers\ProvidersRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class ProvidersSync extends Command
{
    /**
     * - Sem argumento: garante que todos os providers do Registry existam no DB
     * - Com {code}: garante só aquele code no DB
     *
     * Ex:
     * php artisan providers:sync
     * php artisan providers:sync worldslot
     */
    protected $signature = 'providers:sync {code?} {--enable : Marca como enabled (apenas se criar)}';
    protected $description = 'Garante/valida game_providers no DB e (em dev) pode listar providersList sem quebrar produção';

    public function handle(ProvidersRegistry $registry): int
    {
        $codeArg = $this->argument('code');
        $codes = $codeArg ? [strtolower((string) $codeArg)] : $registry->supportedCodes();

        $created = 0;
        $updated = 0;
        $errors  = 0;

        foreach ($codes as $code) {
            $this->info(">> Provider: {$code}");

            try {
                // 1) Garante registro no DB
                /** @var GameProvider $row */
                $row = GameProvider::query()->firstOrNew(['code' => $code]);

                $wasNew = !$row->exists;

                if ($wasNew) {
                    $row->name = strtoupper($code);
                    $row->enabled = (bool) $this->option('enable'); // padrão: false
                    $row->base_url = $row->base_url ?? null;
                    $row->credentials_json = $row->credentials_json ?? [];
                    $row->save();
                    $created++;

                    $this->line(json_encode([
                        'ok' => true,
                        'action' => 'created',
                        'code' => $code,
                        'enabled' => (bool) $row->enabled,
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                } else {
                    // normaliza mínimos sem sobrescrever dados reais
                    $dirty = false;

                    if (empty($row->name)) {
                        $row->name = strtoupper($code);
                        $dirty = true;
                    }

                    if ($row->credentials_json === null) {
                        $row->credentials_json = [];
                        $dirty = true;
                    }

                    if ($dirty) {
                        $row->save();
                        $updated++;
                    }

                    $this->line(json_encode([
                        'ok' => true,
                        'action' => $dirty ? 'updated' : 'exists',
                        'code' => $code,
                        'enabled' => (bool) $row->enabled,
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }

                // 2) Validação segura:
                // Em produção, NÃO chama resolve/providersList automaticamente (evita 500 sem credenciais)
                if (!App::environment('production')) {
                    try {
                        if ($row->enabled) {
                            $p = $registry->resolve($code);
                            $list = $p->providersList();

                            $this->line(json_encode([
                                'ok' => true,
                                'action' => 'providers_list',
                                'code' => $code,
                                'count' => is_array($list) ? count($list) : null,
                            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                        } else {
                            $this->line(json_encode([
                                'ok' => true,
                                'action' => 'skipped_providers_list',
                                'reason' => 'disabled_in_db',
                                'code' => $code,
                            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                        }
                    } catch (\Throwable $e) {
                        $this->warn("Aviso (providersList falhou): {$e->getMessage()}");
                    }
                } else {
                    $this->line(json_encode([
                        'ok' => true,
                        'action' => 'production_safe_skip',
                        'code' => $code,
                        'note' => 'Em produção este comando só garante/normaliza o DB. Não chama providersList.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
            } catch (\Throwable $e) {
                $errors++;
                $this->error("ERRO: {$e->getMessage()}");
            }
        }

        $this->info("== DONE == created={$created} updated={$updated} errors={$errors}");

        return $errors > 0 ? 1 : 0;
    }
}