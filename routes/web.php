<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;

// HARDCODED OVERRIDE - FORÇA AS CHAVES NA MEMÓRIA
config(['app.key' => 'base64:OTY4N2Y1ZTM0YjI5ZDVhZDVmOTU1ZTM2ZDU4NTQ=']);
config(['app.cipher' => 'AES-256-CBC']);
config(['jwt.secret' => 'OTY4N2Y1ZTM0YjI5ZDVhZDVmOTU1ZTM2ZDU4NTQ=']);

Route::get('/', function () {
    return "<h1>SYSTEM ONLINE</h1><p>The core application is now operational.</p>";
});

// CARREGA AS ROTAS DO SISTEMA
if (file_exists(__DIR__ . '/groups/layouts/app.php')) {
    include_once(__DIR__ . '/groups/layouts/app.php');
}


