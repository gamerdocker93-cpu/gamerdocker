<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SpinConfigs;

class SpinConfigsInit extends Command
{
    protected $signature = 'spin:init';
    protected $description = 'Cria o primeiro registro em ggds_spin_config (se nao existir)';

    public function handle()
    {
        $row = SpinConfigs::first();

        if (!$row) {
            SpinConfigs::create([
                'is_active' => true,
                'config' => [],
            ]);

            $this->info('spin_configs criado (vazio).');
            return Command::SUCCESS;
        }

        $this->info('spin_configs ja existe.');
        return Command::SUCCESS;
    }
}