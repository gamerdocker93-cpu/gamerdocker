<?php

use App\Http\Controllers\Api\Wallet\DepositController;
use App\Http\Controllers\Api\Gateways\StripeController;
use Illuminate\Support\Facades\Route;

Route::prefix('deposit')
    ->group(function ()
    {
        /*
        |--------------------------------------------------------------------------
        | Depósitos padrão (Pix / gateways atuais)
        |--------------------------------------------------------------------------
        */
        Route::get('/', [DepositController::class, 'index']);
        Route::post('/payment', [DepositController::class, 'submitPayment']);

        /*
        |--------------------------------------------------------------------------
        | Stripe - Criar Checkout
        |--------------------------------------------------------------------------
        | Front chama:
        | POST /api/wallet/deposit/stripe/checkout
        */
        Route::post('/stripe/checkout', [StripeController::class, 'createCheckout']);
    });