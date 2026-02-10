<?php

namespace App\Jobs;

use App\Models\AffiliateWithdraw;
use App\Models\Gateway;
use App\Models\Setting;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ProcessAutoWithdrawal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Evita job travar demais
     */
    public int $timeout = 120;

    /**
     * Quantidade máxima por execução (fallback)
     */
    protected int $defaultBatchSize = 20;

    public function handle(): void
    {
        // trava simples pra não rodar 2 jobs ao mesmo tempo (mesmo em retry)
        $lock = Cache::lock('auto_withdraw:lock', 120);
        if (! $lock->get()) {
            return;
        }

        try {
            $setting = Setting::first();
            if (! $setting) {
                Log::warning('AutoWithdraw: Setting não encontrado.');
                return;
            }

            // Colunas reais no seu settings:
            // - auto_withdraw_enabled (global)
            // - auto_withdraw_players (players)
            // - auto_withdraw_affiliates (afiliados)
            $autoEnabled = (int)($setting->auto_withdraw_enabled ?? 0) === 1;

            // Se você ainda não estiver usando os toggles separados, pode deixar fallback:
            $autoPlayers = (int)($setting->auto_withdraw_players ?? 0) === 1;
            $autoAff     = (int)($setting->auto_withdraw_affiliates ?? 0) === 1;

            // fallback caso exista alguma coluna antiga que você tenha criado em algum momento
            if (! $autoPlayers && isset($setting->auto_withdraw_player_enabled)) {
                $autoPlayers = (int)($setting->auto_withdraw_player_enabled ?? 0) === 1;
            }
            if (! $autoAff && isset($setting->auto_withdraw_affiliate_enabled)) {
                $autoAff = (int)($setting->auto_withdraw_affiliate_enabled ?? 0) === 1;
            }

            // regra: se global estiver OFF, não roda nada
            if (! $autoEnabled) {
                return;
            }

            // Se global ON mas os dois toggles específicos estão OFF, não faz nada
            if (! $autoPlayers && ! $autoAff) {
                return;
            }

            $batchSize = (int)($setting->auto_withdraw_batch_size ?? $this->defaultBatchSize);
            if ($batchSize <= 0) {
                $batchSize = $this->defaultBatchSize;
            }

            // preferência do gateway para payout: 'sharkpay' | 'digitopay'
            $preferred = strtolower((string)($setting->auto_withdraw_gateway ?? 'sharkpay'));

            $gateway = Gateway::first();
            if (! $gateway) {
                Log::warning('AutoWithdraw: Gateway (tabela gateways) não encontrado.');
                return;
            }

            // Checagens simples: só tenta pagar se o gateway estiver habilitado no settings
            // (isso evita tentar payout com gateway “desligado” no admin)
            if ($preferred === 'sharkpay' && (int)($setting->sharkpay_is_enable ?? 0) !== 1) {
                Log::warning('AutoWithdraw: sharkpay selecionado, mas sharkpay_is_enable está OFF.');
                return;
            }
            if ($preferred === 'digitopay' && (int)($setting->digitopay_is_enable ?? 0) !== 1) {
                Log::warning('AutoWithdraw: digitopay selecionado, mas digitopay_is_enable está OFF.');
                return;
            }

            $withdrawalsHasInQueue = Schema::hasColumn('withdrawals', 'in_queue');
            $affiliateHasInQueue   = Schema::hasColumn('affiliate_withdraws', 'in_queue');

            if ($autoPlayers) {
                $this->processUserWithdrawals($gateway, $preferred, $batchSize, $withdrawalsHasInQueue);
            }

            if ($autoAff) {
                $this->processAffiliateWithdrawals($gateway, $preferred, $batchSize, $affiliateHasInQueue);
            }
        } finally {
            optional($lock)->release();
        }
    }

    protected function processUserWithdrawals(Gateway $gateway, string $preferred, int $batchSize, bool $hasInQueue): void
    {
        $query = Withdrawal::query()
            ->where('status', 0)
            ->orderBy('id', 'asc');

        if ($hasInQueue) {
            $query->where(function ($q) {
                $q->whereNull('in_queue')->orWhere('in_queue', 0);
            });
        }

        $withdrawals = $query->limit($batchSize)->get();

        foreach ($withdrawals as $w) {
            $this->markProcessing($w, $hasInQueue);

            try {
                if ($w->type !== 'pix') {
                    $this->markFailed($w, $hasInQueue, 'Tipo não suportado (somente pix).');
                    continue;
                }

                $user = User::find($w->user_id);
                if (! $user) {
                    $this->markFailed($w, $hasInQueue, 'Usuário não encontrado.');
                    continue;
                }

                $ok = false;

                if ($preferred === 'sharkpay') {
                    $ok = $this->payoutSharkPay(
                        $gateway,
                        (float)$w->amount,
                        (string)$w->pix_key,
                        (string)$w->pix_type,
                        (string)($user->cpf ?? ''),
                        (string)($user->email ?? ''),
                        (string)($user->name ?? 'Cliente'),
                        $w
                    );
                } elseif ($preferred === 'digitopay') {
                    $this->markFailed($w, $hasInQueue, 'Digitopay auto saque ainda não implementado.');
                    continue;
                } else {
                    $this->markFailed($w, $hasInQueue, 'Gateway inválido em auto_withdraw_gateway.');
                    continue;
                }

                if ($ok) {
                    $this->markPaid($w, $hasInQueue);
                } else {
                    $this->markFailed($w, $hasInQueue, 'Falha ao pagar no provedor.');
                }
            } catch (\Throwable $e) {
                Log::error('AutoWithdraw: erro em withdrawals', [
                    'withdrawal_id' => $w->id,
                    'message' => $e->getMessage(),
                ]);
                $this->markFailed($w, $hasInQueue, 'Exceção: ' . $e->getMessage());
            }
        }
    }

    protected function processAffiliateWithdrawals(Gateway $gateway, string $preferred, int $batchSize, bool $hasInQueue): void
    {
        $query = AffiliateWithdraw::query()
            ->where('status', 0)
            ->orderBy('id', 'asc');

        if ($hasInQueue) {
            $query->where(function ($q) {
                $q->whereNull('in_queue')->orWhere('in_queue', 0);
            });
        }

        $withdrawals = $query->limit($batchSize)->get();

        foreach ($withdrawals as $w) {
            $this->markProcessing($w, $hasInQueue);

            try {
                if ($w->type !== 'pix') {
                    $this->markFailed($w, $hasInQueue, 'Tipo não suportado (somente pix).');
                    continue;
                }

                $user = User::find($w->user_id);
                if (! $user) {
                    $this->markFailed($w, $hasInQueue, 'Usuário não encontrado.');
                    continue;
                }

                $ok = false;

                if ($preferred === 'sharkpay') {
                    $ok = $this->payoutSharkPay(
                        $gateway,
                        (float)$w->amount,
                        (string)$w->pix_key,
                        (string)$w->pix_type,
                        (string)($user->cpf ?? ''),
                        (string)($user->email ?? ''),
                        (string)($user->name ?? 'Afiliado'),
                        $w
                    );
                } elseif ($preferred === 'digitopay') {
                    $this->markFailed($w, $hasInQueue, 'Digitopay auto saque ainda não implementado.');
                    continue;
                } else {
                    $this->markFailed($w, $hasInQueue, 'Gateway inválido em auto_withdraw_gateway.');
                    continue;
                }

                if ($ok) {
                    $this->markPaid($w, $hasInQueue);
                } else {
                    $this->markFailed($w, $hasInQueue, 'Falha ao pagar no provedor.');
                }
            } catch (\Throwable $e) {
                Log::error('AutoWithdraw: erro em affiliate_withdraws', [
                    'affiliate_withdraw_id' => $w->id,
                    'message' => $e->getMessage(),
                ]);
                $this->markFailed($w, $hasInQueue, 'Exceção: ' . $e->getMessage());
            }
        }
    }

    /**
     * Payout via SharkPay (sem depender de auth('api'), funciona dentro do Job).
     */
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

        // 1) token
        $auth = Http::withBasicAuth($public, $private)->post($baseUri . 'auth');
        if (! $auth->successful()) {
            Log::warning('AutoWithdraw SharkPay: falha /auth', [
                'status' => $auth->status(),
                'body' => $auth->body(),
            ]);
            return false;
        }

        $json  = $auth->json();
        $token = $json['success']['token'] ?? null;

        if (empty($token)) {
            Log::warning('AutoWithdraw SharkPay: token vazio.');
            return false;
        }

        // 2) pixout
        $keytype = null;

        if (class_exists(\App\Helpers\Core::class) && method_exists(\App\Helpers\Core::class, 'checkPixKeyTypeSharkPay')) {
            $keytype = \App\Helpers\Core::checkPixKeyTypeSharkPay($pixKey);
        } else {
            $keytype = $pixType ?: 'random';
        }

        $payload = [
            'amount'      => (float)$amount,
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

        if (! $resp->successful()) {
            Log::warning('AutoWithdraw SharkPay: falha pixout/create', [
                'status' => $resp->status(),
                'body' => $resp->body(),
            ]);
            return false;
        }

        $data = $resp->json();

        $reference = $data['withdraw']['reference']
            ?? $data['withdraw']['txid']
            ?? $data['reference']
            ?? null;

        // tenta salvar payment_id se existir na tabela
        if (!empty($reference) && isset($withdrawModel->payment_id)) {
            try {
                $withdrawModel->payment_id = $reference;
                $withdrawModel->save();
            } catch (\Throwable $e) {
                // ignora
            }
        }

        // se provedor aceitou a requisição, consideramos ok
        return true;
    }

    protected function markProcessing($model, bool $hasInQueue): void
    {
        if ($hasInQueue && isset($model->in_queue)) {
            try {
                $model->in_queue = 1;
                $model->save();
            } catch (\Throwable $e) {
                // ignora
            }
        }
    }

    protected function markPaid($model, bool $hasInQueue): void
    {
        try {
            $model->status = 1;

            if ($hasInQueue && isset($model->in_queue)) {
                $model->in_queue = 2;
            }

            $model->save();
        } catch (\Throwable $e) {
            // ignora
        }
    }

    protected function markFailed($model, bool $hasInQueue, string $reason): void
    {
        Log::warning('AutoWithdraw: falhou', [
            'id' => $model->id ?? null,
            'reason' => $reason,
        ]);

        // Mantém status 0 pra reprocessar depois, mas libera in_queue
        if ($hasInQueue && isset($model->in_queue)) {
            try {
                $model->in_queue = 0;
                $model->save();
            } catch (\Throwable $e) {
                // ignora
            }
        }
    }
}