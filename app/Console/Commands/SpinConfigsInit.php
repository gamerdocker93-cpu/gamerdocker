<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SpinConfigs;

class SpinConfigsInit extends Command
{
    protected $signature = 'spin:init';
    protected $description = 'Cria o primeiro registro em spin_configs (se não existir)';

    public function handle()
    {
        $row = SpinConfigs::first();

        if (!$row) {
            SpinConfigs::create([]);
            $this->info('spin_configs criado (vazio).');
            return Command::SUCCESS;
        }

        $this->info('spin_configs já existe.');
        return Command::SUCCESS;
    }
}
