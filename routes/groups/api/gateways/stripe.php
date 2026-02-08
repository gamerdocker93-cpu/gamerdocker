<?php

use App\Http\Controllers\Api\Gateways\StripeController;
use Illuminate\Support\Facades\Route;

/**
 * STRIPE WEBHOOK
 * URL FINAL (no Railway):
 * https://SEU_DOMINIO/webhooks/stripe
 *
 * OBS: esse arquivo será incluído no routes/api.php
 */
Route::prefix('webhooks')
    ->group(function () {
        Route::post('stripe', [StripeController::class, 'webhooks']);
    });
