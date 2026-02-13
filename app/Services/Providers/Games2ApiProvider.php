<?php

namespace App\Services\Providers;

class Games2ApiProvider extends BaseArtisanProvider
{
    public function providersList(): array
    {
        $out = $this->call('games2api:providers');
        return ['raw' => $out];
    }

    public function gamesList(): array
    {
        $out = $this->call('games2api:games');
        return ['raw' => $out];
    }
}
