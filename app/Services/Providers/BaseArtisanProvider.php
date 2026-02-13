<?php

namespace App\Services\Providers;

use App\Models\GameProvider;
use Illuminate\Support\Facades\Artisan;

abstract class BaseArtisanProvider implements ProviderInterface
{
    public function __construct(protected GameProvider $provider) {}

    protected function call(string $signature, array $params = []): string
    {
        Artisan::call($signature, $params);
        return (string) Artisan::output();
    }
}
