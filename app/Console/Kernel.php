<?php

namespace App\Console;

use App\Jobs\ProcessAutoWithdrawal;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Registra comandos manualmente (garante que existam no php artisan).
     */
    protected $commands = [
        \App\Console\Commands\TempAdminCreate::class,
        \App\Console\Commands\FixAdminRole::class,
        \App\Console\Commands\GamesKeysInit::class,
        \App\Console\Commands\SpinConfigsInit::class,
        \App\Console\Commands\DemoSeedIfEmpty::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new ProcessAutoWithdrawal)
            ->everyMinute()
            ->name('process-auto-withdrawal')
            ->withoutOverlapping(5);
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}