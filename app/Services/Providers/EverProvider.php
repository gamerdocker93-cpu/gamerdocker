<?php

namespace App\Services\Providers;

class EverProvider extends BaseArtisanProvider
{
    public function providersList(): array
    {
        // se não existir command de providers, retorna vazio
        return [];
    }

    public function gamesList(): array
    {
        $out = $this->call('ever:games');
        return ['raw' => $out];
    }
}
