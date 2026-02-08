<?php

namespace App\Services\Gateways;

use App\Helpers\Core as Helper;
use App\Models\Gateway;
use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

/**
 * StripeService
 *
 * Responsável por criar Checkout Session e padronizar payload/metadata
 * para o fluxo de depósito via Stripe.
 */
class StripeService
{
    /**
     * Cria uma Checkout Session do Stripe para depósito.
     *
     * @param  float|int|string  $amount  Valor do depósito (ex: 10.50)
     * @param  array             $meta    Metadata extra (opcional)
     * @return array{status:bool, session_id?:string, url?:string, message?:string}
     */
    public static function createCheckout($amount, array $meta = []): array
    {
        try {
            // Precisa do stripe-php instalado no composer.
            if (!class_exists(\Stripe\Stripe::class)) {
                return [
                    'status' => false,
                    'message' => 'Stripe SDK (stripe-php) não instalado no backend.',
                ];
            }

            // Chaves (aceita tanto STRIPE_SECRET quanto STRIPE_SECRET_KEY)
            $secret = env('STRIPE_SECRET') ?: env('STRIPE_SECRET_KEY');
            if (empty($secret)) {
                return [
                    'status' => false,
                    'message' => 'STRIPE_SECRET (ou STRIPE_SECRET_KEY) não configurada.',
                ];
            }

            // Auth do usuário (rota deve estar em auth.jwt)
            $user = auth('api')->user();
            if (!$user) {
                return [
                    'status' => false,
                    'message' => 'Usuário não autenticado.',
                ];
            }

            // Setting (min/max etc.)
            $setting = Helper::getSetting();
            $min = isset($setting->min_deposit) ? (float) $setting->min_deposit : 0.0;
            $max = isset($setting->max_deposit) ? (float) $setting->max_deposit : 999999999.0;

            $amount = (float) str_replace(',', '.', (string) $amount);
            if ($amount <= 0) {
                return ['status' => false, 'message' => 'Valor inválido.'];
            }
            if ($amount < $min) {
                return ['status' => false, 'message' => "Valor mínimo é {$min}."];
            }
            if ($amount > $max) {
                return ['status' => false, 'message' => "Valor máximo é {$max}."];
            }

            // Gateway config (DB)
            $gateway = Gateway::first();
            $currency = $setting->currency_code ?? 'BRL';

            // URL base (Railway)
            $appUrl = rtrim(config('app.url') ?: env('APP_URL', ''), '/');
            if (empty($appUrl)) {
                // fallback hard: tenta montar baseado no host atual
                $appUrl = rtrim(request()->getSchemeAndHttpHost(), '/');
            }

            // Rotas do front (hash mode) — usa as páginas já existentes no teu projeto
            $successUrl = $appUrl . '/#/stripe/success?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl  = $appUrl . '/#/stripe/cancel';

            // Cria uma Transaction "pendente" pra rastrear no webhook
            $transaction = Transaction::create([
                'user_id'         => $user->id,
                'payment_method'  => 'stripe',
                'price'           => $amount,
                'currency'        => $currency,
                'status'          => 0,
            ]);

            // Configura Stripe
            \Stripe\Stripe::setApiKey($secret);

            $amountCents = (int) round($amount * 100);

            // Metadata essencial p/ webhook achar a transação
            $metadata = array_merge([
                'transaction_id' => (string) $transaction->id,
                'user_id'        => (string) $user->id,
                'type'           => 'deposit',
            ], $meta);

            $session = \Stripe\Checkout\Session::create([
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url'  => $cancelUrl,

                'customer_email' => $user->email,

                'line_items' => [
                    [
                        'quantity' => 1,
                        'price_data' => [
                            'currency' => strtolower($currency),
                            'unit_amount' => $amountCents,
                            'product_data' => [
                                'name' => 'Depósito',
                                'description' => 'Recarga na plataforma',
                            ],
                        ],
                    ],
                ],

                'metadata' => $metadata,
            ]);

            // Salva o session_id na Transaction (se existir a coluna).
            // Se não existir, não quebra.
            try {
                if (isset($transaction->session_id)) {
                    $transaction->session_id = $session->id;
                    $transaction->save();
                }
            } catch (\Throwable $e) {
                // ignora
            }

            return [
                'status' => true,
                'session_id' => $session->id,
                'url' => $session->url,
            ];
        } catch (\Throwable $e) {
            Log::error('StripeService::createCheckout error', [
                'message' => $e->getMessage(),
            ]);

            return [
                'status' => false,
                'message' => 'Erro ao criar checkout Stripe: ' . $e->getMessage(),
            ];
        }
    }
}
