<?php

namespace App\Console;

use App\Jobs\ProcessAutoWithdrawal;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /*
     * Registra comandos manualmente (garante que existam no php artisan).
     * Mantive os seus e adicionei os comandos de provedores (Games/*)
     * para não dar "Command not found" no Railway.
     */
    protected $commands = [
        \App\Console\Commands\TempAdminCreate::class,
        \App\Console\Commands\FixAdminRole::class,
        \App\Console\Commands\GamesKeysInit::class,
        \App\Console\Commands\SpinConfigsInit::class,
        \App\Console\Commands\DemoSeedIfEmpty::class,
        \App\Console\Commands\GamesCacheClear::class,

        /*
         * Comandos de provedores (Games)
         * Se algum não existir no seu repo, remova só a linha dele.
         */
        \App\Console\Commands\Games\EverGamesList::class,
        \App\Console\Commands\Games\EverProviderList::class,

        \App\Console\Commands\Games\FiversGamesList::class,
        \App\Console\Commands\Games\FiversProviderList::class,

        \App\Console\Commands\Games\Games2ApiList::class,
        \App\Console\Commands\Games\Games2ApiProviderList::class,

        \App\Console\Commands\Games\PlayGamingGamesList::class,

        \App\Console\Commands\Games\SalsaGameList::class,

        \App\Console\Commands\Games\VenixGamesList::class,
        \App\Console\Commands\Games\VenixProviderList::class,

        \App\Console\Commands\Games\WorldSlotGamesList::class,
        \App\Console\Commands\Games\WorldSlotProviderList::class,
    ];

    /*
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new ProcessAutoWithdrawal)
            ->everyMinute()
            ->name('process-auto-withdrawal')
            ->withoutOverlapping(5);
    }

    /*
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}