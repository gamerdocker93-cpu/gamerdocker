<?php

use App\Http\Controllers\Layouts\ApplicationController;
use Illuminate\Support\Facades\Route;

/**
 * SPA fallback (Vue Router history mode)
 * - NÃO pode capturar /api, /build, /storage, /assets
 * - captura todo o resto e entrega o layout do app
 */
Route::get('/{view?}', ApplicationController::class)
    ->where('view', '^(?!api|build|storage|assets).*$');