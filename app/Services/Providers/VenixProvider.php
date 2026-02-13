<?php

namespace App\Services\Providers;

class VenixProvider extends BaseArtisanProvider
{
    public function providersList(): array
    {
        return [];
    }

    public function gamesList(): array
    {
        $out = $this->call('venix:games');
        return ['raw' => $out];
    }
}
