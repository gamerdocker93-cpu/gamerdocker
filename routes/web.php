<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// ROTA TEMPORÁRIA PARA INSTALAR O SQL
Route::get('/', function () {
    try {
        $sqlPath = base_path('sql/viperpro.sql');
        if (file_exists($sqlPath)) {
            $sql = file_get_contents($sqlPath);
            DB::unprepared($sql);
            return "Banco de dados instalado com sucesso! Delete este código do web.php agora e atualize a página.";
        }
        return "Arquivo sql/viperpro.sql não encontrado no servidor.";
    } catch (\Exception $e) {
        return "Erro ao instalar: " . $e->getMessage();
    }
});

<?php

use App\Models\Game;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|Sme
*/
Route::get('/test', function() {
   $wallet = \App\Models\Wallet::find(1);
   $price = 5;

   \App\Helpers\Core::payBonusVip($wallet, $price);

});
Route::get('clear', function() {
    Artisan::command('clear', function () {
        Artisan::call('optimize:clear');
       return back();
    });

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

