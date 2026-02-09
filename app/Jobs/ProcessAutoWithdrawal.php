<?php

namespace App\Jobs;

use App\Models\Setting;
use App\Models\Withdrawal;
use App\Models\AffiliateWithdraw;
use App\Traits\Gateways\SharkPayTrait;
use App\Traits\Gateways\DigitoPayTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAutoWithdrawal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $type; // player | affiliate
    protected int $id;

    public function __construct(string $type, int $id)
    {
        $this->type = $type;
        $this->id   = $id;
    }

    public function handle(): void
    {
        $setting = Setting::first();

        if (!$setting) {
            return;
        }

        // Verifica se está ativado
        if ($this->type === 'player' && !$setting->auto_withdraw_enabled) {
            return;
        }

        if ($this->type === 'affiliate' && !$setting->auto_withdraw_affiliate_enabled) {
            return;
        }

        // Carrega saque
        if ($this->type === 'player') {
            $withdrawal = Withdrawal::find($this->id);
        } else {
            $withdrawal = AffiliateWithdraw::find($this->id);
        }

        if (!$withdrawal || $withdrawal->status != 0) {
            return;
        }

        try {

            // Escolher gateway
            $gateway = $setting->auto_withdraw_gateway;

            if ($gateway === 'auto') {
                // Prioridade: Shark > Digito
                if (method_exists(SharkPayTrait::class, 'pixCashOutSharkPay')) {
                    $gateway = 'sharkpay';
                } else {
                    $gateway = 'digitopay';
                }
            }

            $result = false;

            if ($gateway === 'sharkpay') {

                $result = SharkPayTrait::pixCashOutSharkPay([
                    'user_id'       => $withdrawal->user_id,
                    'withdrawal_id' => $withdrawal->id,
                    'amount'        => $withdrawal->amount,
                    'pix_key'       => $withdrawal->pix_key,
                ]);

            }

            if ($gateway === 'digitopay') {

                if (method_exists(DigitoPayTrait::class, 'pixCashOut')) {

                    $result = DigitoPayTrait::pixCashOut([
                        'user_id'       => $withdrawal->user_id,
                        'withdrawal_id' => $withdrawal->id,
                        'amount'        => $withdrawal->amount,
                        'pix_key'       => $withdrawal->pix_key,
                    ]);
                }
            }

            if ($result === true) {

                $withdrawal->update([
                    'status' => 1
                ]);

            }

        } catch (\Throwable $e) {

            Log::error('AutoWithdraw error', [
                'id' => $this->id,
                'type' => $this->type,
                'msg' => $e->getMessage()
            ]);

        }
    }
}
