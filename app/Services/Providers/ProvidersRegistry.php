<?php

namespace App\Services\Providers;

use App\Models\GameProvider;
use Illuminate\Support\Facades\App;
use InvalidArgumentException;

class ProvidersRegistry
{
    /** @var array<string, class-string<ProviderInterface>> */
    protected array $map = [
        // Fake (teste sem provedor real)
        'testprovider' => \App\Services\Providers\FakeProvider::class,

        // Reais
        'ever'       => EverProvider::class,
        'venix'      => VenixProvider::class,
        'worldslot'  => WorldSlotProvider::class,
        'playgaming' => PlayGamingProvider::class,
        'games2api'  => Games2ApiProvider::class,
        // 'fivers' => FiversProvider::class,
    ];

    /**
     * Permite registrar providers dinamicamente (ex.: ServiceProvider).
     */
    public function register(string $code, string $class): self
    {
        $code = strtolower(trim($code));

        if (!is_subclass_of($class, ProviderInterface::class)) {
            throw new InvalidArgumentException("Classe {$class} não implementa ProviderInterface.");
        }

        $this->map[$code] = $class;

        return $this;
    }

    public function resolve(string $code): ProviderInterface
    {
        $code = strtolower(trim($code));

        // DB é a fonte de verdade: precisa existir e estar habilitado
        $provider = GameProvider::query()->where('code', $code)->first();

        if (!$provider) {
            throw new InvalidArgumentException("Provider não existe na tabela game_providers: {$code}");
        }

        if (!$provider->enabled) {
            throw new InvalidArgumentException("Provider '{$code}' está desabilitado.");
        }

        // 1) Se existir provider mapeado, usa ele
        if (isset($this->map[$code])) {
            $class = $this->map[$code];

            // resolve via container (permite injetar deps)
            return App::make($class, ['provider' => $provider]);
        }

        /**
         * 2) Fallback seguro:
         * - Em produção: NÃO permite provider sem implementação
         * - Fora de produção: usa FakeProvider para testes
         */
        if (App::environment('production')) {
            throw new InvalidArgumentException("Provider não registrado no Registry: {$code}");
        }

        // Se FakeProvider existir, usa ele
        if (class_exists(\App\Services\Providers\FakeProvider::class)) {
            return App::make(\App\Services\Providers\FakeProvider::class, ['provider' => $provider]);
        }

        throw new InvalidArgumentException("Provider '{$code}' não registrado e FakeProvider não encontrado.");
    }

    /**
     * Lista apenas os códigos com implementação real (map).
     * Útil para comandos tipo providers:sync criarem defaults.
     */
    public function supportedCodes(): array
    {
        // Se quiser EXCLUIR o testprovider dessa lista, descomente o filtro abaixo:
        // return array_values(array_filter(array_keys($this->map), fn ($c) => $c !== 'testprovider'));

        return array_keys($this->map);
    }

    /**
     * Lista todos os códigos cadastrados no banco.
     */
    public function dbCodes(): array
    {
        return GameProvider::query()
            ->pluck('code')
            ->map(fn ($c) => strtolower((string) $c))
            ->values()
            ->all();
    }
}