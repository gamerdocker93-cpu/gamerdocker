<?php

namespace App\Console\Commands\Games;

use App\Traits\Commands\Games\FiversGamesCommandTrait;
use Illuminate\Console\Command;

class FiversGamesList extends Command
{
    use FiversGamesCommandTrait;

    protected $signature = 'fivers:games-list';
    protected $description = 'Lista/importa jogos do provedor Fivers (safe, não derruba scheduler)';

    public function handle(): int
    {
        $this->info('FIVERS: iniciando fivers:games-list (safe mode)...');

        $code = self::getGames();

        $this->info('FIVERS: finalizado (safe).');
        return $code; // sempre 0 no trait (SUCCESS)
    }
}