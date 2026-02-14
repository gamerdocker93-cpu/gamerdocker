<?php

namespace App\Services\Providers;

use App\Models\GameProvider;

/**
 * Provider Fake (para teste sem contrato com provedor real).
 * - Não chama API externa
 * - Retorna uma lista fixa de "providers" e "games"
 * - Respeita enabled/base_url/credentials_json via GameProvider model
 */
class FakeProvider implements ProviderInterface
{
    public function __construct(protected GameProvider $provider)
    {
    }

    /**
     * Lista de provedores do agregador (fake).
     * Mantém o formato simples para você validar o pipeline.
     */
    public function providersList(): array
    {
        return [
            [
                'code' => $this->provider->code,
                'name' => $this->provider->name ?: 'Fake Provider',
                'enabled' => (bool) $this->provider->enabled,
                'base_url' => $this->provider->baseUrl(),
            ],
        ];
    }

    /**
     * Lista de jogos (fake).
     * Campos pensados para você conseguir mapear no seu importer depois.
     */
    public function gamesList(): array
    {
        $prefix = $this->provider->code ?: 'fake';

        // Exemplo: usa credentials_json só pra você validar que está chegando como array
        $token = (string) ($this->provider->credential('token', ''));

        return [
            [
                'provider_code' => $prefix,
                'game_id'       => $prefix . '_demo_01',
                'game_code'     => $prefix . '_demo_01',
                'game_name'     => 'Fake Demo Game 01',
                'game_type'     => 'slot',
                'distribution'  => 'fake',
                'rtp'           => 96.5,
                'is_mobile'     => true,
                'has_demo'      => true,
                'meta'          => [
                    'token_present' => $token !== '',
                ],
            ],
            [
                'provider_code' => $prefix,
                'game_id'       => $prefix . '_demo_02',
                'game_code'     => $prefix . '_demo_02',
                'game_name'     => 'Fake Demo Game 02',
                'game_type'     => 'slot',
                'distribution'  => 'fake',
                'rtp'           => 95.1,
                'is_mobile'     => true,
                'has_demo'      => true,
                'meta'          => [
                    'token_present' => $token !== '',
                ],
            ],
        ];
    }
}
