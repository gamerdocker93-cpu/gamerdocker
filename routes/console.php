<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


/**
 * Testa a fila SEM tinker, despachando um Job real e logando.
 * Uso:
 *   php artisan queue:test
 */
Artisan::command('queue:test', function () {
    $connection = config('queue.default');

    $this->info("QUEUE_CONNECTION (queue.default) = {$connection}");

    // Se estiver usando database, garante que a tabela jobs existe
    if ($connection === 'database' && !Schema::hasTable('jobs')) {
        $this->error("A tabela 'jobs' não existe. Rode:");
        $this->line("php artisan queue:table && php artisan migrate --force");
        return 1;
    }

    // Dispara Job real (sem closure)
    dispatch(new \App\Jobs\TestQueueJob());

    Log::info('queue:test - Job TestQueueJob foi despachado.');

    $this->info('OK: Job despachado. Agora veja se o worker executa e aparece nos logs:');
    $this->line("- Procure por: 'TestQueueJob EXECUTADO com sucesso'");
    return 0;
})->purpose('Dispara um Job de teste na fila e confirma execução via logs.');