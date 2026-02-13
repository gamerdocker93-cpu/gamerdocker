<?php

namespace App\Services\Providers;

class PlayGamingProvider extends BaseArtisanProvider
{
    public function providersList(): array
    {
        return [];
    }

    public function gamesList(): array
    {
        $out = $this->call('playgaming:games');
        return ['raw' => $out];
    }
}
