<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| IMPORTANTE (PRODUÇÃO):
|--------------------------------------------------------------------------
| NÃO force app.key/app.cipher/jwt.secret aqui.
| Isso sobrescreve o ENV correto (Railway) e quebra o Encrypter do Laravel.
| APP_KEY / APP_CIPHER / JWT_SECRET devem vir do ambiente (.env / Railway Variables).
|
| Removido:
| config(['app.key' => ...]);
| config(['app.cipher' => ...]);
| config(['jwt.secret' => ...]);
|
*/

/**
 * Healthcheck simples (opcional)
 */
Route::get('/health', function () {
    return response()->json(['ok' => true]);
});

/**
 * SPA fallback (Vue Router history mode)
 * Qualquer rota que NÃO seja /api/* vai renderizar o Blade.
 */
Route::get('/{any}', function () {
    return view('layouts.app');
})->where('any', '^(?!api).*$');