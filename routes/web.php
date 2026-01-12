<?php

use Illuminate\Support\Facades\Route;

// Rota inicial simples para testar se o site abriu
Route::get('/', function () {
    return view('welcome'); // Ou a rota principal do seu jogo
});

// Suas rotas originais
include_once(__DIR__ . '/groups/layouts/app.php');


