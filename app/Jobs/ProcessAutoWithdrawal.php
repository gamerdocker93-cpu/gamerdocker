<?php

namespace App\Jobs;

use App\Models\Gateway;
use App\Models\Setting;
use App\Models\User;
use App\Models\Withdrawal;
use App\Models\AffiliateWithdraw;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAutoWithdrawal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    protected int $defaultBatchSize = 20;

    public function handle(): void
    {
        // ==========================
        // HEARTBEAT (prova definitiva)
        // ==========================
        $hbName = 'process-auto-withdrawal';
        $hbStart = microtime(true);
        $hbError = null;

        try {
            $setting = Setting::first();
            if (!$setting) {
                Log::warning('AutoWithdraw: Setting não encontrado.');
                return;
            }

            // MASTER + TOGGLES
            $masterEnabled = (bool) ($setting->auto_withdraw_enabled ?? false);

            // players (usuarios)
            $autoPlayersEnabled = (bool) ($setting->auto_withdraw_players ?? false);

            // afiliados (vamos deixar pronto, mas você vai ligar depois)
            $autoAffEnabled = (bool) ($setting->auto_withdraw_affiliates ?? false);
            $autoAffMaster  = (bool) ($setting->auto_withdraw_affiliate_enabled ?? false); // nome certo da coluna

            if (!$masterEnabled) {
                // master OFF = não roda nada
                return;
            }

            // Agora: vamos rodar SOMENTE PLAYERS (uma de cada vez, como você pediu)
            if (!$autoPlayersEnabled) {
                return;
            }

            $batchSize = (int) ($setting->auto_withdraw_batch_size ?? $this->defaultBatchSize);
            if ($batchSize <= 0) $batchSize = $this->defaultBatchSize;

            $preferred = strtolower((string) ($setting->auto_withdraw_gateway ?? 'sharkpay'));

            $gateway = Gateway::first();
            if (!$gateway) {
                Log::warning('AutoWithdraw: Gateway (tabela gateways) não encontrado.');
                return;
            }

            // Apenas SAQUES DE PLAYERS
            $this->processUserWithdrawals($gateway, $preferred, $batchSize);

            // (Afiliados fica pra próxima etapa)
            // if ($autoAffMaster && $autoAffEnabled) {
            //     $this->processAffiliateWithdrawals($gateway, $preferred, $batchSize);
            // }

        } catch (\Throwable $e) {
            $hbError = $e->getMessage();

            Log::error('AutoWithdraw: erro no job ProcessAutoWithdrawal', [
                'message' => $e->getMessage(),
            ]);

            // mantém comportamento normal do Laravel (para aparecer nos logs como falha)
            throw $e;
        } finally {
            // grava heartbeat SEMPRE, mesmo se return cedo ou erro
            $runtimeMs = (int) round((microtime(true) - $hbStart) * 1000);

            try {
                // updateOrInsert sempre funciona em Query Builder
                DB::table('scheduler_heartbeats')->updateOrInsert(
                    ['name' => $hbName],
                    [
                        'last_ran_at' => now(),
                        // increment usando raw
                        'runs' => DB::raw('runs + 1'),
                        'last_runtime_ms' => $runtimeMs,
                        'last_error' => $hbError,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                Log::info('HEARTBEAT: process-auto-withdrawal', [
                    'runtime_ms' => $runtimeMs,
                    'error' => $hbError,
                ]);
            } catch (\Throwable $e) {
                // Se não existir tabela ainda, não pode derrubar o job.
                Log::warning('HEARTBEAT: falhou ao gravar em scheduler_heartbeats (ok ignorar)', [
                    'err' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function processUserWithdrawals(Gateway $gateway, string $preferred, int $batchSize): void
    {
        $query = Withdrawal::query()
            ->where('status', 0)
            ->orderBy('id', 'asc');

        if (Schema::hasColumn('withdrawals', 'in_queue')) {
            $query->where(function ($q) {
                $q->whereNull('in_queue')->orWhere('in_queue', 0);
            });
        }

        $withdrawals = $query->limit($batchSize)->get();

        foreach ($withdrawals as $w) {
            $this->markProcessing($w, 'withdrawals');

            try {
                if ($w->type !== 'pix') {
                    $this->markFailed($w, 'withdrawals', 'Tipo não suportado (somente pix).');
                    continue;
                }

                $user = User::find($w->user_id);
                if (!$user) {
                    $this->markFailed($w, 'withdrawals', 'Usuário não encontrado.');
                    continue;
                }

                $ok = false;

                if ($preferred === 'sharkpay') {
                    $ok = $this->payoutSharkPay(
                        $gateway,
                        (float) $w->amount,
                        (string) $w->pix_key,
                        (string) $w->pix_type,
                        (string) ($user->cpf ?? ''),
                        (string) ($user->email ?? ''),
                        (string) ($user->name ?? 'Cliente'),
                        $w
                    );
                } elseif ($preferred === 'digitopay') {
                    $this->markFailed($w, 'withdrawals', 'Digitopay auto saque ainda não implementado.');
                    continue;
                } else {
                    $this->markFailed($w, 'withdrawals', 'Gateway inválido em auto_withdraw_gateway.');
                    continue;
                }

                if ($ok) {
                    $this->markPaid($w, 'withdrawals');
                } else {
                    $this->markFailed($w, 'withdrawals', 'Falha ao pagar no provedor.');
                }
            } catch (\Throwable $e) {
                Log::error('AutoWithdraw: erro em withdrawals', [
                    'withdrawal_id' => $w->id,
                    'message' => $e->getMessage(),
                ]);
                $this->markFailed($w, 'withdrawals', 'Exceção: ' . $e->getMessage());
            }
        }
    }

    protected function processAffiliateWithdrawals(Gateway $gateway, string $preferred, int $batchSize): void
    {
        $query = AffiliateWithdraw::query()
            ->where('status', 0)
            ->orderBy('id', 'asc');

        if (Schema::hasColumn('affiliate_withdraws', 'in_queue')) {
            $query->where(function ($q) {
                $q->whereNull('in_queue')->orWhere('in_queue', 0);
            });
        }

        $withdrawals = $query->limit($batchSize)->get();

        foreach ($withdrawals as $w) {
            $this->markProcessing($w, 'affiliate_withdraws');

            try {
                if ($w->type !== 'pix') {
                    $this->markFailed($w, 'affiliate_withdraws', 'Tipo não suportado (somente pix).');
                    continue;
                }

                $user = User::find($w->user_id);
                if (!$user) {
                    $this->markFailed($w, 'affiliate_withdraws', 'Usuário não encontrado.');
                    continue;
                }

                $ok = false;

                if ($preferred === 'sharkpay') {
                    $ok = $this->payoutSharkPay(
                        $gateway,
                        (float) $w->amount,
                        (string) $w->pix_key,
                        (string) $w->pix_type,
                        (string) ($user->cpf ?? ''),
                        (string) ($user->email ?? ''),
                        (string) ($user->name ?? 'Afiliado'),
                        $w
                    );
                } elseif ($preferred === 'digitopay') {
                    $this->markFailed($w, 'affiliate_withdraws', 'Digitopay auto saque ainda não implementado.');
                    continue;
                } else {
                    $this->markFailed($w, 'affiliate_withdraws', 'Gateway inválido em auto_withdraw_gateway.');
                    continue;
                }

                if ($ok) {
                    $this->markPaid($w, 'affiliate_withdraws');
                } else {
                    $this->markFailed($w, 'affiliate_withdraws', 'Falha ao pagar no provedor.');
                }
            } catch (\Throwable $e) {
                Log::error('AutoWithdraw: erro em affiliate_withdraws', [
                    'withdrawal_id' => $w->id,
                    'message' => $e->getMessage(),
                ]);
                $this->markFailed($w, 'affiliate_withdraws', 'Exceção: ' . $e->getMessage());
            }
        }
    }

    protected function payoutSharkPay(
        Gateway $gateway,
        float $amount,
        string $pixKey,
        string $pixType,
        string $document,
        string $email,
        string $name,
        $withdrawModel
    ): bool {
        $public  = $gateway->getAttributes()['shark_public_key'] ?? null;
        $private = $gateway->getAttributes()['shark_private_key'] ?? null;

        if (empty($public) || empty($private)) {
            Log::warning('AutoWithdraw SharkPay: chaves não configuradas no DB gateways.');
            return false;
        }

        $baseUri = 'https://sharkpay.com.br/api/';

        $auth = Http::withBasicAuth($public, $private)->post($baseUri . 'auth');
        if (!$auth->successful()) {
            Log::warning('AutoWithdraw SharkPay: falha /auth', [
                'status' => $auth->status(),
                'body' => $auth->body(),
            ]);
            return false;
        }

        $json = $auth->json();
        $token = $json['success']['token'] ?? null;
        if (empty($token)) {
            Log::warning('AutoWithdraw SharkPay: token vazio.');
            return false;
        }

        $keytype = null;
        if (class_exists(\App\Helpers\Core::class) && method_exists(\App\Helpers\Core::class, 'checkPixKeyTypeSharkPay')) {
            $keytype = \App\Helpers\Core::checkPixKeyTypeSharkPay($pixKey);
        } else {
            $keytype = $pixType ?: 'random';
        }

        $payload = [
            'amount'      => (float) $amount,
            'pixkey'      => $pixKey,
            'keytype'     => $keytype,
            'document'    => $document,
            'email'       => $email,
            'description' => $name,
        ];

        $resp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ])->post($baseUri . 'pixout/create', $payload);

        if (!$resp->successful()) {
            Log::warning('AutoWithdraw SharkPay: falha pixout/create', [
                'status' => $resp->status(),
                'body' => $resp->body(),
            ]);
            return false;
        }

        $data = $resp->json();

        $reference = $data['withdraw']['reference'] ?? $data['withdraw']['txid'] ?? $data['reference'] ?? null;

        if (!empty($reference) && isset($withdrawModel->payment_id)) {
            try {
                $withdrawModel->payment_id = $reference;
                $withdrawModel->save();
            } catch (\Throwable $e) {
                // ignora
            }
        }

        $status = $data['withdraw']['status'] ?? null;
        if ($status === 'PAID' || $status === 1 || $status === '1') {
            return true;
        }

        return true;
    }

    protected function markProcessing($model, string $table): void
    {
        if (Schema::hasColumn($table, 'in_queue') && isset($model->in_queue)) {
            try {
                $model->in_queue = 1;
                $model->save();
            } catch (\Throwable $e) {
                // ignora
            }
        }
    }

    protected function markPaid($model, string $table): void
    {
        try {
            $model->status = 1;

            if (Schema::hasColumn($table, 'in_queue') && isset($model->in_queue)) {
                $model->in_queue = 2;
            }

            $model->save();
        } catch (\Throwable $e) {
            // ignora
        }
    }

    protected function markFailed($model, string $table, string $reason): void
    {
        Log::warning('AutoWithdraw: falhou', [
            'table' => $table,
            'id' => $model->id ?? null,
            'reason' => $reason,
        ]);

        if (Schema::hasColumn($table, 'in_queue') && isset($model->in_queue)) {
            try {
                $model->in_queue = 0;
                $model->save();
            } catch (\Throwable $e) {
                // ignora
            }
        }
    }
}