<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

// FORÇA O LOG PARA O TERMINAL (EVITA ERRO DE PERMISSÃO EM ARQUIVO)
Config::set('logging.default', 'errorlog');

Route::get('/', function () {
    try {
        // Tenta encontrar o SQL na pasta que você mostrou
        $path = base_path('sql/viperpro.sql');
        
        if (file_exists($path)) {
            $sql = file_get_contents($path);
            DB::unprepared($sql);
            Artisan::call('optimize:clear');
            return "<h1>SISTEMA DESTRANCADO!</h1><p>Banco de dados instalado. Agora remova este codigo para jogar.</p>";
        }
        return "Arquivo SQL nao encontrado na pasta /sql/.";
    } catch (\Exception $e) {
        if (str_contains($e->getMessage(), 'already exists')) {
            return "<h1>SISTEMA ONLINE</h1><p>O banco ja esta pronto. Remova este instalador do web.php.</p>";
        }
        return "Erro: " . $e->getMessage();
    }
});

// SUAS ROTAS ORIGINAIS (MANTIDAS)
include_once(__DIR__ . '/groups/layouts/app.php');


