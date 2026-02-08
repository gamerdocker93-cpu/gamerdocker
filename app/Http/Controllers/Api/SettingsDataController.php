<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gateway;
use Illuminate\Http\JsonResponse;

class SettingsDataController extends Controller
{
    public function __invoke(): JsonResponse
    {
        // Pega settings do Helper (no seu projeto ele já existe)
        $setting = \Helper::getSetting();
        $setting = is_array($setting) ? $setting : [];

        // Pega gateway (chaves) do banco
        $gateway = Gateway::first();

        // Flags (liga/desliga) calculadas por presença de credenciais
        $stripeEnabled = $gateway
            && !empty($gateway->getAttributes()['stripe_public_key'] ?? null)
            && !empty($gateway->getAttributes()['stripe_secret_key'] ?? null);

        $sharkEnabled = $gateway
            && !empty($gateway->getAttributes()['shark_public_key'] ?? null)
            && !empty($gateway->getAttributes()['shark_private_key'] ?? null);

        $digitopayEnabled = $gateway
            && !empty($gateway->digitopay_uri ?? null)
            && !empty($gateway->digitopay_cliente_id ?? null)
            && !empty($gateway->digitopay_cliente_secret ?? null);

        // Coloca no mesmo objeto que o front salva em localStorage (setting)
        $setting['stripe_is_enable'] = $stripeEnabled;
        $setting['shark_is_enable'] = $sharkEnabled;
        $setting['digitopay_is_enable'] = $digitopayEnabled;

        // Opcional: expor public key do Stripe pro front (não expõe secret)
        if ($gateway) {
            $setting['stripe_public_key'] = $gateway->getAttributes()['stripe_public_key'] ?? null;
        }

        return response()->json([
            'setting' => $setting,
        ]);
    }
}
