<?php

namespace App\Http\Controllers\Api\Gateways;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeController extends Controller
{
    /**
     * Webhook Stripe
     * URL: POST /webhooks/stripe
     */
    public function webhooks(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        $secret = env('STRIPE_WEBHOOK_SECRET') ?: env('STRIPE_WEBHOOK_KEY');

        try {

            // ===============================
            // Validar evento (se tiver secret)
            // ===============================
            if (
                !empty($secret) &&
                !empty($sigHeader) &&
                class_exists(\Stripe\Webhook::class)
            ) {

                $event = \Stripe\Webhook::constructEvent(
                    $payload,
                    $sigHeader,
                    $secret
                );

            } else {

                // Sem validação (modo dev)
                $event = json_decode($payload, false);

                Log::warning('Stripe webhook UNVERIFIED', [
                    'has_secret' => !empty($secret),
                    'has_sig'    => !empty($sigHeader),
                ]);
            }

            // ===============================
            // Tipo do evento
            // ===============================
            $type = $event->type ?? null;

            Log::info('Stripe webhook received', [
                'type' => $type,
            ]);

            // ===============================
            // Pagamento concluído
            // ===============================
            if ($type === 'checkout.session.completed') {

                $session = $event->data->object ?? null;

                if (!$session) {
                    return response()->json(['ok' => true], 200);
                }

                $metadata = (array) ($session->metadata ?? []);

                $transactionId = $metadata['transaction_id'] ?? null;

                if (!$transactionId) {
                    Log::warning('Stripe: transaction_id não encontrado', [
                        'metadata' => $metadata
                    ]);

                    return response()->json(['ok' => true], 200);
                }

                // ===============================
                // Busca transação
                // ===============================
                $transaction = Transaction::where('id', $transactionId)
                    ->where('status', 0)
                    ->first();

                if (!$transaction) {
                    Log::info('Stripe: transação já processada', [
                        'id' => $transactionId,
                    ]);

                    return response()->json(['ok' => true], 200);
                }

                // ===============================
                // Marca transação como paga
                // ===============================
                $transaction->update([
                    'status' => 1,
                ]);

                // ===============================
                // Cria depósito (se não existir)
                // ===============================
                $deposit = Deposit::where('payment_id', $transaction->id)->first();

                if (!$deposit) {
                    Deposit::create([
                        'payment_id' => $transaction->id,
                        'user_id'    => $transaction->user_id,
                        'amount'     => $transaction->price,
                        'type'       => 'stripe',
                        'currency'   => $transaction->currency,
                        'symbol'     => $transaction->currency,
                        'status'     => 1,
                    ]);
                }

                // ===============================
                // Credita carteira
                // ===============================
                $wallet = Wallet::where('user_id', $transaction->user_id)->first();

                if ($wallet) {
                    $wallet->increment('balance', $transaction->price);
                }

                Log::info('Stripe pagamento confirmado', [
                    'transaction_id' => $transactionId,
                    'user_id'        => $transaction->user_id,
                    'amount'         => $transaction->price,
                ]);
            }

            // Stripe precisa receber 200
            return response()->json(['received' => true], 200);

        } catch (\Throwable $e) {

            Log::error('Stripe webhook error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            // Retorna 200 pra não ficar reenviando
            return response()->json(['received' => true], 200);
        }
    }
}
