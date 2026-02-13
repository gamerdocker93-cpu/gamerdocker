<?php

namespace App\Services\Providers;

use App\Models\GameProvider;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class ProvidersRegistry
{
    /** @var array<string, class-string<ProviderInterface>> */
    protected array $map = [
        'ever'       => EverProvider::class,
        'venix'      => VenixProvider::class,
        'worldslot'  => WorldSlotProvider::class,
        'playgaming' => PlayGamingProvider::class,
        'games2api'  => Games2ApiProvider::class,
        // 'fivers' => FiversProvider::class, // você ativa quando tiver
    ];

    public function resolve(string $code): ProviderInterface
    {
        $code = strtolower(trim($code));

        if (!isset($this->map[$code])) {
            throw new InvalidArgumentException("Provider não registrado no Registry: {$code}");
        }

        $provider = GameProvider::where('code', $code)->first();
        if (!$provider) {
            throw new InvalidArgumentException("Provider não existe na tabela game_providers: {$code}");
        }

        if (!$provider->enabled) {
            throw new InvalidArgumentException("Provider '{$code}' está desabilitado.");
        }

        $class = $this->map[$code];

        return new $class($provider);
    }

    public function supportedCodes(): array
    {
        return array_keys($this->map);
    }
}
