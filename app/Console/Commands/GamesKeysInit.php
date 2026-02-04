<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GamesKey;

class GamesKeysInit extends Command
{
    protected $signature = 'gameskeys:init';
    protected $description = 'Cria o primeiro registro em games_keys (se não existir)';

    public function handle()
    {
        $row = GamesKey::first();

        if (!$row) {
            GamesKey::create([
                'api_endpoint' => '',
                'agent_code' => '',
                'agent_token' => '',
                'agent_secret_key' => '',
            ]);

            $this->info('games_keys criado (vazio).');
            return 0;
        }

        $this->info('games_keys já existe.');
        return 0;
    }
}
