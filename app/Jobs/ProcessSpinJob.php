<?php

namespace App\Jobs;

use App\Models\Spin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessSpinJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $timeout = 60;

    public function __construct(public string $requestId) {}

    public function handle(): void
    {
        $spin = Spin::where('request_id', $this->requestId)->first();
        if (!$spin) return;

        $spin->update(['status' => 'processing']);

        try {
            // ⚠️ AQUI: no futuro você chama o provedor real (Slotegrator/Salsa/etc).
            // Por enquanto, um resultado fake só pra validar pipeline.
            $result = [
                'win' => 0,
                'currency' => 'BRL',
                'balance_change' => 0,
                'reels' => [],
                'ts' => now()->toISOString(),
            ];

            $spin->update([
                'status' => 'done',
                'result' => $result,
                'error'  => null,
            ]);
        } catch (Throwable $e) {
            $spin->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
