<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class GamesCacheClear extends Command
{
    protected $signature = 'games:cache-clear';
    protected $description = 'Limpa cache relacionado a games (usa tags se suportar, senão flush)';

    public function handle(): int
    {
        try {
            Cache::tags(['games'])->flush();
            $this->info('Cache games limpo (tags).');
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            try {
                Cache::flush();
                $this->warn('Cache games limpo (flush total, driver sem tags).');
                return Command::SUCCESS;
            } catch (\Throwable $e2) {
                $this->error('Falha ao limpar cache: ' . $e2->getMessage());
                return Command::FAILURE;
            }
        }
    }
}
