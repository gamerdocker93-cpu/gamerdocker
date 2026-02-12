<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use App\Models\Game;

class DemoSeedIfEmpty extends Command
{
    protected $signature = 'demo:seed-if-empty';
    protected $description = 'Roda o DemoGamesSeeder somente se a tabela games estiver vazia';

    public function handle(): int
    {
        if (!Schema::hasTable('games')) {
            $this->warn('Tabela games nao existe. Abortando.');
            return Command::SUCCESS;
        }

        try {
            $count = Game::query()->count();
        } catch (\Throwable $e) {
            $this->error('Falha ao contar games: ' . $e->getMessage());
            return Command::FAILURE;
        }

        if ($count > 0) {
            $this->info('Games ja existem (' . $count . '). Nada a fazer.');
            return Command::SUCCESS;
        }

        $this->info('Tabela games vazia. Rodando DemoGamesSeeder...');
        $exitCode = $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\DemoGamesSeeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            $this->error('Seeder falhou.');
            return Command::FAILURE;
        }

        $this->info('Seeder executado com sucesso.');
        return Command::SUCCESS;
    }
}
