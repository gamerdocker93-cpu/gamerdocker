<?php

namespace App\Http\Controllers\Api\Gateways;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeController
{
    /**
     * Webhook Stripe
     * URL: POST /webhooks/stripe
     */
    public function webhooks(Request $request)
    {
        // Payload bruto
        $payload = $request->getContent();

        // Se você tiver webhook secret, dá pra validar assinatura.
        // Se não tiver ainda, a gente só loga e retorna 200 (pra não quebrar o fluxo).
        $sigHeader = $request->header('Stripe-Signature');
        $secret = env('STRIPE_WEBHOOK_SECRET') ?: env('STRIPE_WEBHOOK_KEY');

        try {
            if (!empty($secret) && !empty($sigHeader) && class_exists(\Stripe\Webhook::class)) {
                // Valida assinatura do Stripe
                $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
                $eventType = $event->type ?? null;

                Log::info('Stripe webhook received (verified)', [
                    'type' => $eventType,
                ]);

                // TODO: aqui depois a gente implementa:
                // - localizar Transaction/Deposit pelo metadata (ex: invoice_no / user_id)
                // - marcar como pago
                // - creditar Wallet
            } else {
                // Sem validação (modo "placeholder" até você configurar a Stripe)
                $data = json_decode($payload, true);

                Log::warning('Stripe webhook received (UNVERIFIED - missing secret or stripe-php)', [
                    'has_secret' => !empty($secret),
                    'has_signature' => !empty($sigHeader),
                    'stripe_php_installed' => class_exists(\Stripe\Webhook::class),
                    'type' => $data['type'] ?? null,
                ]);
            }

            return response()->json(['received' => true], 200);
        } catch (\Throwable $e) {
            Log::error('Stripe webhook error', [
                'message' => $e->getMessage(),
            ]);

            // Importante: Stripe reenvia se não for 2xx
            // Mas por enquanto vamos retornar 200 para não causar loop de retries no Railway.
            return response()->json(['received' => true], 200);
        }
    }
}
