<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SpinConfigs;

class SpinInit extends Command
{
    protected $signature = 'spin:init';
    protected $description = 'Cria o primeiro registro em spin_configs (se não existir)';

    public function handle()
    {
        $row = SpinConfigs::first();

        if (!$row) {
            SpinConfigs::create([]); // cria com defaults / nullable
            $this->info('spin_configs criado (vazio).');
            return 0;
        }

        $this->info('spin_configs já existe.');
        return 0;
    }
}
