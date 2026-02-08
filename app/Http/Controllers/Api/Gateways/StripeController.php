<?php

namespace App\Http\Controllers\Api\Gateways;

use App\Http\Controllers\Controller;
use App\Models\Gateway;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Core as Helper;

class StripeController extends Controller
{
    /**
     * Cria uma Checkout Session da Stripe (PIX/Cartão conforme conta Stripe).
     * FRONT chama esse endpoint para obter a URL do checkout e redirecionar.
     *
     * Recomendação de rota:
     * POST /api/wallet/deposit/stripe/checkout
     */
    public function createCheckout(Request $request)
    {
        try {
            if (!class_exists(\Stripe\StripeClient::class)) {
                return response()->json([
                    'message' => 'Stripe SDK (stripe-php) não está instalado no backend.'
                ], 500);
            }

            $setting = Helper::getSetting();
            $setting = is_array($setting) ? $setting : [];

            $minDeposit = isset($setting['min_deposit']) ? floatval($setting['min_deposit']) : 1;
            $maxDeposit = isset($setting['max_deposit']) ? floatval($setting['max_deposit']) : 999999;

            $rules = [
                'amount' => ['required', 'numeric', 'min:' . $minDeposit, 'max:' . $maxDeposit],
                'accept_bonus' => ['nullable', 'boolean'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            /** @var User $user */
            $user = auth('api')->user();
            if (!$user) {
                return response()->json(['message' => 'Não autenticado'], 401);
            }

            // Stripe exige centavos como inteiro
            $amount = floatval($request->amount);
            $amountCents = (int) round($amount * 100);

            // Moeda: usa setting->currency_code se existir, senão BRL
            $currency = strtolower($setting['currency_code'] ?? 'BRL');

            // Checar se tem chaves Stripe no DB (gateways) ou em env
            $gateway = Gateway::first();
            $stripeSecret = null;

            // Se seu banco tiver campos stripe_* mas seu Model esconde, usamos getAttributes()
            if ($gateway) {
                $attrs = $gateway->getAttributes();
                $stripeSecret = $attrs['stripe_secret_key'] ?? $attrs['stripe_secret'] ?? null;
            }
            $stripeSecret = $stripeSecret ?: env('STRIPE_SECRET') ?: env('STRIPE_SECRET_KEY');

            if (!$stripeSecret) {
                return response()->json([
                    'message' => 'Stripe secret key não configurada. Preencha no banco (gateways) ou env STRIPE_SECRET.'
                ], 400);
            }

            // Cria Transaction pendente no seu padrão do sistema (igual Pix)
            $orderData = [
                'user_id' => $user->id,
                'payment_method' => 'stripe',
                'price' => $amount,
                'currency' => strtoupper($currency),
                'accept_bonus' => (bool) ($request->accept_bonus ?? false),
                'status' => 0,
            ];

            $transaction = Transaction::create($orderData);

            // URLs de retorno (o seu front está em HASH mode)
            $appUrl = rtrim(env('APP_URL', url('/')), '/');
            $successUrl = $appUrl . '/#/stripe/success?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl  = $appUrl . '/#/stripe/cancel';

            $stripe = new \Stripe\StripeClient($stripeSecret);

            $session = $stripe->checkout->sessions->create([
                'mode' => 'payment',
                'payment_method_types' => ['card'], // se sua Stripe habilitar outros, a gente ajusta depois
                'customer_email' => $user->email,
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => $currency,
                        'unit_amount' => $amountCents,
                        'product_data' => [
                            'name' => 'Depósito - ' . ($setting['software_name'] ?? 'Plataforma'),
                        ],
                    ],
                ]],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'metadata' => [
                    'transaction_id' => (string) $transaction->id,
                    'user_id' => (string) $user->id,
                ],
            ]);

            // Salva o session_id na Transaction pra rastrear (opcional)
            $transaction->update([
                'payment_id' => $session->id,
                'reference'  => $session->payment_intent ?? null,
            ]);

            return response()->json([
                'status' => true,
                'checkout_url' => $session->url,
                'session_id' => $session->id,
                'transaction_id' => $transaction->id,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Stripe createCheckout error', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Erro ao criar checkout Stripe.',
            ], 500);
        }
    }

    /**
     * Webhook Stripe
     * URL: POST /webhooks/stripe   (conforme seu routes/groups/gateways/stripe.php)
     */
    public function webhooks(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        $secret = env('STRIPE_WEBHOOK_SECRET') ?: env('STRIPE_WEBHOOK_KEY');

        try {
            if (!class_exists(\Stripe\Webhook::class)) {
                Log::warning('Stripe webhook: stripe-php não instalado.');
                return response()->json(['received' => true], 200);
            }

            if (empty($secret) || empty($sigHeader)) {
                Log::warning('Stripe webhook UNVERIFIED: faltando STRIPE_WEBHOOK_SECRET ou assinatura', [
                    'has_secret' => !empty($secret),
                    'has_signature' => !empty($sigHeader),
                ]);
                // Mesmo sem validar, retornamos 200 pra não criar loop
                return response()->json(['received' => true], 200);
            }

            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
            $type = $event->type ?? null;

            Log::info('Stripe webhook received (verified)', [
                'type' => $type,
            ]);

            /**
             * Eventos mais comuns:
             * - checkout.session.completed (quando finaliza o checkout)
             * - payment_intent.succeeded (pagamento confirmado)
             */
            if ($type === 'checkout.session.completed') {
                $session = $event->data->object;

                $transactionId = $session->metadata->transaction_id ?? null;
                if ($transactionId) {
                    $this->completeTransactionIfNeeded($transactionId);
                }
            }

            if ($type === 'payment_intent.succeeded') {
                $pi = $event->data->object;
                // Se você setar metadata no payment_intent também, dá pra pegar por aqui.
                // Por enquanto deixamos via checkout.session.completed.
            }

            return response()->json(['received' => true], 200);
        } catch (\Throwable $e) {
            Log::error('Stripe webhook error', [
                'message' => $e->getMessage(),
            ]);
            // Retorna 200 para não ficar em retry infinito no Railway durante setup.
            return response()->json(['received' => true], 200);
        }
    }

    /**
     * Finaliza a transação no banco e credita saldo.
     * Isso aqui é o “equivalente” ao CompleteDeposit do Pix.
     */
    private function completeTransactionIfNeeded($transactionId): void
    {
        $transaction = Transaction::where('id', $transactionId)->first();
        if (!$transaction) {
            Log::warning('Stripe: Transaction não encontrada', ['transaction_id' => $transactionId]);
            return;
        }

        if ((int) $transaction->status === 1) {
            return; // já pago
        }

        $setting = Helper::getSetting();
        $setting = is_array($setting) ? $setting : [];

        $user = User::find($transaction->user_id);
        if (!$user) return;

        $wallet = Wallet::where('user_id', $transaction->user_id)->first();
        if (!$wallet) return;

        // Regras de crédito (mantém padrão: se rollover desativado vai pra withdrawal, senão vai pra balance)
        $disableRollover = (bool) ($setting['disable_rollover'] ?? false);
        $price = floatval($transaction->price);

        if ($disableRollover) {
            $wallet->increment('balance_withdrawal', $price);
        } else {
            $wallet->increment('balance', $price);
        }

        // Marca como pago
        $transaction->update(['status' => 1]);

        Log::info('Stripe: Transaction completada e saldo creditado', [
            'transaction_id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'amount' => $price,
        ]);
    }
}