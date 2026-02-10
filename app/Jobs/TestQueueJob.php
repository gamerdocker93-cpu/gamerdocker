<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TestQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Tempo máximo (segundos) para este job
    public int $timeout = 60;

    // Número de tentativas
    public int $tries = 1;

    public function handle(): void
    {
        Log::info('TestQueueJob EXECUTADO com sucesso', [
            'ts' => now()->toDateTimeString(),
            'env' => app()->environment(),
            'queue_connection' => config('queue.default'),
        ]);
    }
}
