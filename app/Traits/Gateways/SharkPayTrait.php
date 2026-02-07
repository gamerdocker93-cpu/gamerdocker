<?php

namespace App\Traits\Gateways;

use App\Models\AffiliateHistory;
use App\Models\AffiliateWithdraw;
use App\Models\Deposit;
use App\Models\GamesKey;
use App\Models\Gateway;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Notifications\NewDepositNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Core as Helper;

trait SharkPayTrait
{
    protected static $uriSharkPay = 'https://sharkpay.com.br/api/';


    /**
     * Gera um TXID compatível com Pix (1..35 chars, apenas [A-Za-z0-9]).
     */
    private static function buildTxid(string|int $seed): string
    {
        $raw = 'GD' . (string) $seed . strtoupper(substr(md5((string) $seed . '|' . microtime(true)), 0, 12));
        $raw = preg_replace('/[^A-Za-z0-9]/', '', $raw) ?? '';
        return substr($raw, 0, 35);
    }

    /**
     * Generate Credentials
     * Metodo para gerar credenciais
     * @dev victormsalatiel - Corra de golpista, me chame no instagram
     */
    private static function generateCredentialsSharkPay()
    {
        $gateway = Gateway::first();

        // Segurança: se não existir gateway ou as chaves não estiverem preenchidas, aborta.
        if (empty($gateway)) {
            return false;
        }

        $publicKey  = $gateway->getAttributes()['shark_public_key'] ?? null;
        $privateKey = $gateway->getAttributes()['shark_private_key'] ?? null;

        if (empty($publicKey) || empty($privateKey)) {
            return false;
        }

        $response = Http::withBasicAuth($publicKey, $privateKey)->post(self::$uriSharkPay . 'auth');

        if ($response->successful()) {
            $json = $response->json();
            $token = $json['success']['token'] ?? null;

            if ($token) {
                return $token;
            }

            return false;
        }

        return false;
    }

    /**
     * Request QRCODE
     * Metodo para solicitar uma QRCODE PIX
     * @dev victormsalatiel - Corra de golpista, me chame no instagram
     */
    public static function requestQrcodeSharkPay($request)
    {
        $setting = Helper::getSetting();

        $rules = [
            'amount' => ['required', 'numeric', 'min:' . $setting->min_deposit, 'max:' . $setting->max_deposit],
            'cpf'    => ['required', 'max:255'],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Se não conseguir autenticar, devolve erro claro (evita "Server Error" no front).
        $token = self::generateCredentialsSharkPay();
        if (!$token) {
            return [
                'status' => false,
                'message' => 'SharkPay não autenticou. Verifique se as chaves (shark_public_key / shark_private_key) estão preenchidas na tabela gateways e se a conta está habilitada.',
            ];
        }

        $userId = auth('api')->user()->id;

        // Cria transação/pedido
        $orderData = [
            'user_id'        => $userId,
            'payment_method' => 'pix',
            'price'          => $request->amount,
            'currency'       => $setting->currency_code,
            'accept_bonus'   => $request->accept_bonus,
            'status'         => 0,
        ];

        if (!empty($request->bonus)) {
            $valorFormatado = str_replace(['R$', ' '], '', $request->bonus);
            $orderData['bonus_amount'] = \Helper::amountPrepare($valorFormatado);
        }

        $order = Transaction::create($orderData);
        if (!$order) {
            return ['status' => false, 'message' => 'Não foi possível criar a transação.'];
        }

        // Usa o builder do próprio projeto (já existe no trait)
        $txid = self::buildTxid((int) $order->id, (int) $userId);

        $params = [
            'amount'     => Helper::amountPrepare($request->amount),
            'email'      => auth('api')->user()->email,
            'quantity'   => 1,
            'discount'   => 0,
            'invoice_no' => $order->id,
            'due_date'   => Carbon::now(),
            'tax'        => 0,
            'notes'      => 'Recarga de R$' . $request->amount,
            'item_name'  => 'Recarga',
            'document'   => Helper::soNumero($request->cpf),
            'client'     => auth('api')->user()->name,

            // Compatibilidade: algumas versões usam txid/referenceLabel
            'txid'           => $txid,
            'referenceLabel' => $txid,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ])->post(self::$uriSharkPay . 'pix/create', $params);

        if (!$response->successful()) {
            return [
                'status' => false,
                'message' => 'Falha ao solicitar PIX na SharkPay.',
                'details' => $response->json() ?? $response->body(),
            ];
        }

        $pix = $response->json();

        // Tenta localizar o bloco "invoice" em diferentes formatos de resposta
        $invoice = $pix['invoice'] ?? ($pix['success']['invoice'] ?? ($pix['data']['invoice'] ?? null));
        if (!$invoice && is_array($pix)) {
            $invoice = $pix;
        }

        $txidResponse = $invoice['txid'] ?? ($invoice['id'] ?? ($invoice['transaction_id'] ?? ($invoice['transactionId'] ?? null)));
        $reference    = $invoice['reference'] ?? ($invoice['referenceLabel'] ?? ($invoice['invoice_no'] ?? ($invoice['invoiceNo'] ?? null)));

        // Extrai o "copia e cola" (payload EMV) em diferentes chaves possíveis
        $copy = self::extractPixCopy($invoice, $pix);

        // Se vier placeholder (0503***), o banco recusa o Pix.
        if (is_string($copy) && preg_match('/0503\*{3}/', $copy)) {
            return [
                'status' => false,
                'message' => 'SharkPay retornou um Pix inválido (0503***). Isso normalmente acontece quando a conta/credenciais ainda não estão habilitadas para gerar cobranças reais (compliance/produção).',
            ];
        }

        if (empty($copy)) {
            return [
                'status' => false,
                'message' => 'SharkPay não retornou o código PIX (copia e cola). Verifique as credenciais/ambiente da conta.',
            ];
        }

        // Salva o que tiver (sem depender do formato da API)
        $txidToSave = $txidResponse ?? $txid;
        $referenceToSave = $reference ?? (string) $order->id;

        $checkHash = Helper::GenerateHash('hash:' . $txidToSave, env('DP_PRIVATE_KEY'));
        $order->update([
            'payment_id' => $txidToSave,
            'reference'  => $referenceToSave,
            'hash'       => $checkHash,
        ]);

        self::SharkPayGenerateDeposit($txidToSave, Helper::amountPrepare($request->amount));

        return [
            'status' => true,
            'idTransaction' => $order->id,
            // O front usa isso para renderizar o QRCode e também copiar/colar.
            'qrcode' => $copy,
            'type' => 'pix',
        ];
    }

    /**
     * Extrai o "copia e cola" do PIX (payload EMV) de diferentes formatos de resposta.
     */
    private static function extractPixCopy($invoice, $pix): ?string
    {
        $candidates = [
            'copy',
            'payload',
            'brCode',
            'brcode',
            'emv',
            'pix',
            'copiaecola',
            'copia_e_cola',
            'copyAndPaste',
            'copy_and_paste',
            'qrcode',
            'qr',
        ];

        foreach ($candidates as $key) {
            if (is_array($invoice) && !empty($invoice[$key])) {
                return (string) $invoice[$key];
            }
        }

        foreach ($candidates as $key) {
            if (is_array($pix) && !empty($pix[$key])) {
                return (string) $pix[$key];
            }
        }

        return null;
    }


    /**
     * @param $idTransaction
     * @param $amount
     * @dev victormsalatiel - Corra de golpista, me chame no instagram
     * * Use Digitopay - o melhor gateway de pagamentos para sua plataforma - 048 98814-2566
     * @return void
     */
    private static function SharkPayGenerateDeposit($idTransaction, $amount)
    {
        $userId = auth('api')->user()->id;
        $wallet = Wallet::where('user_id', $userId)->where('active', 1)->first();

        Deposit::create([
            'payment_id' => $idTransaction,
            'user_id'    => $userId,
            'amount'     => $amount,
            'type'       => 'pix',
            'currency'   => $wallet->currency,
            'symbol'     => $wallet->symbol,
            'status'     => 0
        ]);
    }

    /**
     * @param $payment_id
     * @return bool|void
     */
    public static function sharkpayCheckStatus($payment_id)
    {
        if ($token = self::generateCredentialsSharkPay()) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->get(self::$uriSharkPay . 'pix/txid/' . $payment_id);

            if ($response->successful()) {
                $json = $response->json();
                if ($json) {
                    $invoice = $json['invoice'];
                    if ($invoice['status'] == 1) {
                        return true;
                    }
                    return false;
                }
                return false;
            }
            return false;
        }
    }

    /**
     * Consult Status Transaction
     * Consultar o status da transação
     * @dev victormsalatiel - Corra de golpista, me chame no instagram
     *
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public static function consultStatusTransactionSharkpay($idOrder)
    {
        $order = Transaction::find($idOrder);
        if (!empty($order)) {
            if ($token = self::generateCredentialsSharkPay()) {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ])->get(self::$uriSharkPay . 'pix/txid/' . $order->payment_id);

                if ($response->successful()) {
                    $json = $response->json();
                    if ($json) {
                        $invoice = $json['invoice'];

                        if ($invoice['status'] == 1) {
                            if (self::finalizePaymentSharpay($order->payment_id)) {
                                return response()->json(['status' => 'PAID']);
                            }
                        }
                        return response()->json(['status' => 'PENDING']);
                    }
                    return response()->json(['status' => 'PENDING']);
                }
                return response()->json(['status' => 'PENDING']);
            }
        }
    }

    /**
     * @param $idTransaction
     * @return bool|void
     */
    public static function CheckStatus($idTransaction)
    {
        if ($token = self::generateCredentialsSharkPay()) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->get(self::$uriSharkPay . 'pix/txid/' . $idTransaction);

            if ($response->successful()) {
                $json = $response->json();
                if ($json) {
                    $invoice = $json['invoice'];

                    if ($invoice['status'] == 1) {
                        return true;
                    }
                    return false;
                }
                return false;
            }
            return false;
        }
    }

    /**
     * @param $idTransaction
     * @dev victormsalatiel - Corra de golpista, me chame no instagram
     * @return ?bool
     */
    public static function finalizePaymentSharpay($idTransaction): ?bool
    {
        $checkHash = Helper::GenerateHash('hash:' . $idTransaction, env('DP_PRIVATE_KEY'));
        $transaction = Transaction::where('payment_id', $idTransaction)
            ->where('status', 0)
            ->where('hash', $checkHash)
            ->first();

        if (!empty($transaction)) {
            if (self::CheckStatus($idTransaction)) {
                return self::CompleteDeposit($idTransaction);
            }
            return false;
        }

        return false;
    }

    /**
     * @param $idTransaction
     * @return bool
     */
    public static function CompleteDeposit($idTransaction): bool
    {
        $transaction = Transaction::where('payment_id', $idTransaction)->where('status', 0)->first();
        $setting = Helper::getSetting();

        if (!empty($transaction)) {

            /// confirma se realmente foi confirmado
            if (self::sharkpayCheckStatus($transaction->payment_id)) {
                $user = User::find($transaction->user_id);
                $wallet = Wallet::where('user_id', $transaction->user_id)->first();
                if (!empty($wallet)) {

                    $checkTransactions = Transaction::where('user_id', $transaction->user_id)
                        ->where('status', 1)
                        ->count();

                    if ($checkTransactions == 0 || empty($checkTransactions)) {
                        if ($transaction->accept_bonus) {
                            /// pagar o bonus
                            $bonus = Helper::porcentagem_xn($setting->initial_bonus, $transaction->price);
                            $wallet->increment('balance_bonus', $bonus);

                            if (!$setting->disable_rollover) {
                                $wallet->update(['balance_bonus_rollover' => $bonus * $setting->rollover]);
                            }
                        }
                    }

                    /// rollover deposito
                    if ($setting->disable_rollover == false) {
                        $wallet->increment('balance_deposit_rollover', ($transaction->price * intval($setting->rollover_deposit)));
                    }

                    /// acumular bonus
                    Helper::payBonusVip($wallet, $transaction->price);

                    /// quando tiver desativado o rollover, ele manda o dinheiro depositado direto pra carteira de saque
                    if ($setting->disable_rollover) {
                        $wallet->increment('balance_withdrawal', $transaction->price); /// carteira de saque
                    } else {
                        $wallet->increment('balance', $transaction->price); /// carteira de jogos, não permite sacar
                    }

                    if ($transaction->update(['status' => 1])) {
                        $deposit = Deposit::where('payment_id', $idTransaction)->where('status', 0)->first();
                        if (!empty($deposit)) {

                            /// fazer o deposito em cpa
                            $affHistoryCPA = AffiliateHistory::where('user_id', $user->id)
                                ->where('commission_type', 'cpa')
                                //->where('deposited', 1)
                                //->where('status', 0)
                                ->first();

                            if (!empty($affHistoryCPA)) {
                                /// faz uma soma de depositos feitos pelo indicado
                                $affHistoryCPA->increment('deposited_amount', $transaction->price);

                                /// verifcia se já pode receber o cpa
                                $sponsorCpa = User::find($user->inviter);

                                /// verifica se foi pago ou nnão
                                if (!empty($sponsorCpa) && $affHistoryCPA->status == 'pendente') {

                                    if ($affHistoryCPA->deposited_amount >= $sponsorCpa->affiliate_baseline || $deposit->amount >= $sponsorCpa->affiliate_baseline) {
                                        $walletCpa = Wallet::where('user_id', $affHistoryCPA->inviter)->first();

                                        if (!empty($walletCpa)) {

                                            /// paga o valor de CPA
                                            $walletCpa->increment('refer_rewards', $sponsorCpa->affiliate_cpa); /// coloca a comissão
                                            $affHistoryCPA->update(['status' => 1, 'commission_paid' => $sponsorCpa->affiliate_cpa]); /// desativa cpa
                                        }
                                    }
                                }
                            }

                            if ($deposit->update(['status' => 1])) {
                                return true;
                            }
                            return false;
                        }
                        return false;
                    }

                    return false;
                }
            }

            return false;
        }
        return false;
    }

    /**
     * @param $idTransaction
     * @param $amount
     * @dev victormsalatiel - Corra de golpista, me chame no instagram
     * @return void
     */
    private static function generateDepositSharkPay($idTransaction, $amount)
    {
        $userId = auth('api')->user()->id;
        $wallet = Wallet::where('user_id', $userId)->first();

        Deposit::create([
            'payment_id' => $idTransaction,
            'user_id'    => $userId,
            'amount'     => $amount,
            'type'       => 'pix',
            'currency'   => $wallet->currency,
            'symbol'     => $wallet->symbol,
            'status'     => 0
        ]);
    }

    /**
     * @param $idTransaction
     * @param $amount
     * @dev victormsalatiel - Corra de golpista, me chame no instagram
     * @return void
     */
    private static function generateTransactionSharkPay($idTransaction, $amount)
    {
        $setting = Helper::getSetting();

        Transaction::create([
            'payment_id' => $idTransaction,
            'user_id' => auth('api')->user()->id,
            'payment_method' => 'pix',
            'price' => $amount,
            'currency' => $setting->currency_code,
            'status' => 0
        ]);
    }

    /**
     * @param $request
     * @dev victormsalatiel - Corra de golpista, me chame no instagram
     * @return \Illuminate\Http\JsonResponse|void
     */
    public static function pixCashOutSharkPay(array $array): bool
    {
        if ($token = self::generateCredentialsSharkPay()) {
            if (!empty($array['user_id'])) {
                $user = User::find($array['user_id']);
                $params = [
                    'amount'        => Helper::amountPrepare($array['amount']),
                    'pixkey'        => $array['pix_key'],
                    'keytype'       => Helper::checkPixKeyTypeSharkPay($array['pix_key']),
                    'document'      => $user->cpf,
                    'email'         => $user->email,
                    'description'   => $user->name,
                ];

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post(self::$uriSharkPay . 'pixout/create', $params);

                if ($response->successful()) {
                    $responseData = $response->json();

                    if (isset($responseData['withdraw']) && ($responseData['withdraw']['status'] ?? null) == 'PAID') {
                        $withdrawal = Withdrawal::find($array['withdrawal_id']);
                        if (!empty($withdrawal)) {
                            if ($withdrawal->update([
                                'status' => 1,
                                'in_queue' => 2,
                                'payment_id' => $responseData['withdraw']['reference'] ?? null
                            ])) {
                                return true;
                            }
                            return false;
                        }
                    }
                    return false;
                }

                // Em produção, não pode dar dd(); apenas retorna false
                return false;
            }
            return false;
        }

        return false;
    }

    /**
     * @param $request
     * @dev victormsalatiel - Corra de golpista, me chame no instagram
     * @return \Illuminate\Http\JsonResponse|void
     */
    public static function pixCashOutSharkPayAffiliate(array $array): bool
    {
        if ($token = self::generateCredentialsSharkPay()) {
            if (!empty($array['user_id'])) {
                $user = User::find($array['user_id']);
                $params = [
                    'amount'        => Helper::amountPrepare($array['amount']),
                    'pixkey'        => $array['pix_key'],
                    'keytype'       => Helper::checkPixKeyTypeSharkPay($array['pix_key']),
                    'document'      => $user->cpf,
                    'email'         => $user->email,
                    'description'   => $user->name,
                ];

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post(self::$uriSharkPay . 'pixout/create', $params);

                if ($response->successful()) {
                    $responseData = $response->json();

                    if (isset($responseData['withdraw']) && ($responseData['withdraw']['status'] ?? null) == 'PAID') {
                        $withdrawal = AffiliateWithdraw::find($array['withdrawal_id']);
                        if (!empty($withdrawal)) {
                            if ($withdrawal->update([
                                'status' => 1,
                                'in_queue' => 2,
                                'payment_id' => $responseData['withdraw']['reference'] ?? null
                            ])) {
                                return true;
                            }
                            return false;
                        }
                    }
                    return false;
                }

                // Em produção, não pode dar dd(); apenas retorna false
                return false;
            }
            return false;
        }

        return false;
    }
}
