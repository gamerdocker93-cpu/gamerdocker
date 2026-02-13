<?php

use App\Http\Controllers\Layouts\ApplicationController;
use Illuminate\Support\Facades\Route;

/**
 * SPA fallback: qualquer rota que NÃO seja:
 * - api/*
 * - build/*
 * - storage/*
 * - admin/*      (Filament)
 * - livewire/*   (Filament/Livewire)
 * - _internal/*  (rotas internas)
 * - health       / health/* (healthchecks)
 */
Route::get('{view}', ApplicationController::class)
    ->where('view', '^(?!api($|/)|build($|/)|storage($|/)|admin($|/)|livewire($|/)|_internal($|/)|health($|/)).*$');
