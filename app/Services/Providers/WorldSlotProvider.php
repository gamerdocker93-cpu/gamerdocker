<?php

namespace App\Services\Providers;

class WorldSlotProvider extends BaseArtisanProvider
{
    public function providersList(): array
    {
        $out = $this->call('worldslot:providers');
        return ['raw' => $out];
    }

    public function gamesList(): array
    {
        $out = $this->call('worldslot:games');
        return ['raw' => $out];
    }
}
