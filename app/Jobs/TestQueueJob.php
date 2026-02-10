<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TestQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Se quiser, pode ajustar tentativas/timeout aqui
     */
    public $tries = 1;
    public $timeout = 120;

    public function __construct()
    {
        // vazio
    }

    public function handle(): void
    {
        Log::info('✅ TestQueueJob executou com sucesso!', [
            'time' => now()->toDateTimeString(),
            'queue' => $this->queue ?? null,
        ]);
    }
}
