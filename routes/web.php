<?php
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return "<h1>SISTEMA ONLINE</h1><p>Se voce esta vendo isso, as chaves foram aceitas. Agora remova este bloco do web.php para carregar o jogo.</p>";
});

include_once(__DIR__ . '/groups/layouts/app.php');

