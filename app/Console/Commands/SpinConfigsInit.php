<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SpinConfigs;
use Illuminate\Database\QueryException;

class SpinConfigsInit extends Command
{
    protected $signature = 'spin:init';
    protected $description = 'Cria o primeiro registro em ggds_spin_config (se nao existir)';

    public function handle()
    {
        try {
            // Se já existir qualquer registro, não faz nada
            if (SpinConfigs::query()->exists()) {
                $this->info('spin_configs ja existe.');
                return Command::SUCCESS;
            }

            // Cria o primeiro registro alinhado com a migration (is_active + config)
            SpinConfigs::query()->create([
                'is_active' => true,
                'config' => json_encode([
                    'prizes' => [],
                ]),
            ]);

            $this->info('spin_configs criado (vazio).');
            return Command::SUCCESS;

        } catch (QueryException $e) {
            // NÃO derruba o deploy/start por causa disso
            $this->error('spin:init falhou (DB/Schema ainda nao pronto): ' . $e->getMessage());
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('spin:init falhou: ' . $e->getMessage());
            return Command::SUCCESS;
        }
    }
}