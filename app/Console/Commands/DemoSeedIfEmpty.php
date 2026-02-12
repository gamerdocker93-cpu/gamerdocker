<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DemoSeedIfEmpty extends Command
{
    protected $signature = 'demo:seed-if-empty {--force-run=0 : Forca rodar o seeder mesmo se ja tiver games (0 ou 1)}';
    protected $description = 'Roda o DemoGamesSeeder somente se a tabela games estiver vazia (modo seguro)';

    public function handle(): int
    {
        $forceRun = (string) $this->option('force-run') === '1';

        if (!Schema::hasTable('games')) {
            $this->warn('Tabela games nao existe. Abortando.');
            Log::warning('DEMO_SEED: tabela games nao existe, abortando');
            return Command::SUCCESS;
        }

        $lockAcquired = false;

        try {
            $lockName = 'demo_seed_if_empty_lock';

            $lockRow = DB::selectOne("SELECT GET_LOCK(?, 2) AS l", [$lockName]);
            $lockAcquired = isset($lockRow->l) && (int) $lockRow->l === 1;

            if (!$lockAcquired) {
                $this->warn('Lock nao obtido. Outro processo pode estar rodando. Saindo.');
                Log::warning('DEMO_SEED: lock nao obtido, saindo');
                return Command::SUCCESS;
            }

            $count = (int) DB::table('games')->count();

            if (!$forceRun && $count > 0) {
                $msg = 'Games ja existem (' . $count . '). Nada a fazer.';
                $this->info($msg);
                Log::info('DEMO_SEED: ' . $msg);
                return Command::SUCCESS;
            }

            $this->info('Rodando DemoGamesSeeder...');
            Log::info('DEMO_SEED: iniciando db:seed DemoGamesSeeder', ['force_run' => $forceRun]);

            $exitCode = $this->call('db:seed', [
                '--class' => 'Database\\Seeders\\DemoGamesSeeder',
                '--force' => true,
            ]);

            if ($exitCode !== 0) {
                $this->error('Seeder falhou. ExitCode: ' . $exitCode);
                Log::error('DEMO_SEED: seeder falhou', ['exit_code' => $exitCode]);
                return Command::FAILURE;
            }

            $newCount = (int) DB::table('games')->count();
            $this->info('Seeder executado com sucesso. Games agora: ' . $newCount);
            Log::info('DEMO_SEED: sucesso', ['games' => $newCount]);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Falha no demo:seed-if-empty: ' . $e->getMessage());
            Log::error('DEMO_SEED: exception', ['err' => $e->getMessage()]);
            return Command::FAILURE;
        } finally {
            if ($lockAcquired) {
                try {
                    DB::select("SELECT RELEASE_LOCK(?)", ['demo_seed_if_empty_lock']);
                } catch (\Throwable $e) {
                    Log::warning('DEMO_SEED: falha ao liberar lock', ['err' => $e->getMessage()]);
                }
            }
        }
    }
}