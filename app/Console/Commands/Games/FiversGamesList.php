<?php

namespace App\Console\Commands\Games;

use App\Traits\Commands\Games\FiversGamesCommandTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class FiversGamesList extends Command
{
    use FiversGamesCommandTrait;

    protected $signature = 'fivers:games-list';

    protected $description = 'Lista/Importa jogos do provedor Fivers e limpa cache de jogos ao finalizar';

    public function handle(): int
    {
        try {
            $result = self::getGames();

            // Limpa cache de jogos após importar
            try {
                Artisan::call('games:cache-clear');
                $this->info('Cache de jogos limpo com sucesso.');
            } catch (\Throwable $e) {
                // Não quebra a importação se cache falhar
                $this->warn('Falha ao limpar cache (continuando): ' . $e->getMessage());
            }

            $this->info('Comando Fivers finalizado com sucesso.');

            // Se o trait retornar um int, respeita; senão, sucesso
            if (is_int($result)) {
                return $result;
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Erro ao importar/listar jogos Fivers: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}