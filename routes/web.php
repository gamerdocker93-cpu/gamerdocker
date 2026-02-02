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

/*
|--------------------------------------------------------------------------
| Healthcheck
|--------------------------------------------------------------------------
*/
Route::get('/health', function () {
    return response()->json(['ok' => true]);
});


/*
|--------------------------------------------------------------------------
| SPA Fallback (Vue Router - History Mode)
|--------------------------------------------------------------------------
| Qualquer rota que NÃO seja /api/* vai renderizar o Blade principal
*/
Route::get('/{any}', function () {
    return view('layouts.app');
})->where('any', '^(?!api).*$');