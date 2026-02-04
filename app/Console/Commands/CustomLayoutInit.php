<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CustomLayout;

class CustomLayoutInit extends Command
{
    protected $signature = 'customlayout:init';
    protected $description = 'Cria o primeiro registro em custom_layout (se não existir)';

    public function handle()
    {
        $row = CustomLayout::first();

        if (!$row) {
            CustomLayout::create([]); // cria com defaults / nullable
            $this->info('custom_layout criado (vazio).');
            return 0;
        }

        $this->info('custom_layout já existe.');
        return 0;
    }
}
