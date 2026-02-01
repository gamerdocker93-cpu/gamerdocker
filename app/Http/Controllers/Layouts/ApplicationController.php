<?php

namespace App\Http\Controllers\Layouts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    /**
     * SPA entrypoint.
     * Sempre retorna a view principal do Vue.
     */
    public function __invoke(Request $request, $view = null)
    {
        return view('layouts.app');
    }

    // Os métodos abaixo podem ficar, mas não são usados com rota invokable.
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(string $id) {}
    public function edit(string $id) {}
    public function update(Request $request, string $id) {}
    public function destroy(string $id) {}
}