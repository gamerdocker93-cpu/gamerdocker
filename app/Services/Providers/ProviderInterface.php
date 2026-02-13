<?php

namespace App\Services\Providers;

interface ProviderInterface
{
    public function providersList(): array;
    public function gamesList(): array;
}
