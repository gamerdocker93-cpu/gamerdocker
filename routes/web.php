<?php

use App\Models\Game;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| CORRETOR DE PERMISSÕES E INSTALADOR (PARA PLANO FREE)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    try {
        // 1. Tenta liberar as pastas que deram erro no print 1000343531.png
        $storagePath = storage_path();
        $cachePath = base_path('bootstrap/cache');
        @chmod($storagePath, 0777);
        @chmod($cachePath, 0777);

        // 2. Procura o arquivo SQL (testa os dois nomes que vimos na sua pasta)
        $files = ['sql/viperpro.sql', 'sql/viperpro.1.6.1.sql'];
        $foundFile = null;

        foreach ($files as $file) {
            if (file_exists(base_path($file))) {
                $foundFile = $file;
                break;
            }
        }

        if ($foundFile) {
            $sql = file_get_contents(base_path($foundFile));
            DB::unprepared($sql);
            Artisan::call('optimize:clear');
            return "<h1>SUCESSO!</h1><p>Permissoes aplicadas e banco instalado via: $foundFile</p>";
        }

        return "<h1>QUASE LÁ</h1><p>Permissoes aplicadas, mas o arquivo SQL nao foi encontrado na pasta /sql/. Verifique o GitHub.</p>";

    } catch (\Exception $e) {
        // Se as tabelas já existirem, ele cai aqui e apenas limpa o cache
        if (str_contains($e->getMessage(), 'already exists')) {
            Artisan::call('optimize:clear');
            return "<h1>PRONTO!</h1><p>O banco ja estava instalado. Remova este instalador do web.php para o site original carregar.</p>";
        }
        return "<h1>ERRO NA OPERAÇÃO</h1><pre>" . $e->getMessage() . "</pre>";
    }
});

/*
|--------------------------------------------------------------------------
| SUAS ROTAS ORIGINAIS (MANTIDAS INTEGRALMENTE)
|--------------------------------------------------------------------------
*/

Route::get('/test', function() {
   $wallet = \App\Models\Wallet::find(1);
   $price = 5;
   \App\Helpers\Core::payBonusVip($wallet, $price);
});

Route::get('clear', function() {
    Artisan::call('optimize:clear');
    return back();
});

// GAMES PROVIDER
include_once(__DIR__ . '/groups/provider/venix.php');

// GATEWAYS
include_once(__DIR__ . '/groups/gateways/sharkpay.php');

/// SOCIAL
include_once(__DIR__ . '/groups/auth/social.php');

// APP
include_once(__DIR__ . '/groups/layouts/app.php');


