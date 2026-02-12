<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class GamesCacheClear extends Command
{
    protected $signature = 'games:cache-clear';
    protected $description = 'Limpa cache relacionado a jogos (por tag se suportado, senão flush)';

    public function handle(): int
    {
        $clearedByTag = false;

        try {
            Cache::tags(['games'])->flush();
            $clearedByTag = true;
        } catch (\Throwable $e) {
            // Driver não suporta tags (ex: file) ou falhou: cai no fallback
        }

        if (!$clearedByTag) {
            try {
                Cache::flush();
            } catch (\Throwable $e) {
                $this->error('Falha ao limpar cache: ' . $e->getMessage());
                return Command::FAILURE;
            }
        }

        $this->info($clearedByTag ? 'Cache de jogos limpo por tags.' : 'Cache limpo com flush (sem tags).');
        return Command::SUCCESS;
    }
}