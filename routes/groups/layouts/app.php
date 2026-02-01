<?php

use App\Http\Controllers\Layouts\ApplicationController;
use Illuminate\Support\Facades\Route;

// SPA fallback: qualquer rota que NÃO seja api/build/storage
Route::get('{view}', ApplicationController::class)
    ->where('view', '^(?!api|build|storage).*$');