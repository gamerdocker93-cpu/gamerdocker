<?php

namespace App\Services\Providers;

use App\Models\GameProvider;

/**
 * Provider 100% local (sem chamadas externas).
 * Serve para testar fluxo de providers + games:sync sem contrato.
 */
class FakeProvider implements ProviderInterface
{
    protected GameProvider $provider;

    public function __construct(GameProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Lista/validação do provider (providers:sync usa isso).
     * Retorna dados simples e estáveis.
     */
    public function providersList(): array
    {
        return [
            [
                'code' => (string) $this->provider->code,
                'name' => (string) $this->provider->name,
                'enabled' => (bool) $this->provider->enabled,
                'base_url' => (string) ($this->provider->base_url ?? ''),
                'mode' => 'fake',
            ],
        ];
    }

    /**
     * Lista de jogos fake (games:sync usa isso).
     * Retorna jogos mockados, com campos comuns no seu schema.
     */
    public function gamesList(): array
    {
        $code = (string) $this->provider->code;

        // Jogos mockados (você pode aumentar depois)
        $games = [
            $this->game($code, 1001, 'fake_sweet_bonanza', 'Sweet Bonanza (Fake)', 'slot'),
            $this->game($code, 1002, 'fake_gates_olympus', 'Gates of Olympus (Fake)', 'slot'),
            $this->game($code, 1003, 'fake_big_bass', 'Big Bass Bonanza (Fake)', 'slot'),
            $this->game($code, 1004, 'fake_aviator', 'Aviator (Fake)', 'crash'),
            $this->game($code, 1005, 'fake_mines', 'Mines (Fake)', 'instant'),
            $this->game($code, 1006, 'fake_roulette', 'Roulette (Fake)', 'table'),
            $this->game($code, 1007, 'fake_blackjack', 'Blackjack (Fake)', 'table'),
            $this->game($code, 1008, 'fake_dragon_tiger', 'Dragon Tiger (Fake)', 'table'),
            $this->game($code, 1009, 'fake_plinko', 'Plinko (Fake)', 'instant'),
            $this->game($code, 1010, 'fake_book_dead', 'Book of Dead (Fake)', 'slot'),
        ];

        return $games;
    }

    /**
     * Helper: monta um jogo no formato mais compatível possível.
     */
    protected function game(string $providerCode, int $id, string $gameCode, string $name, string $type): array
    {
        return [
            // Identidade
            'provider_code' => $providerCode,
            'game_id'       => (string) $id,
            'game_code'     => $gameCode,
            'game_name'     => $name,
            'game_type'     => $type,

            // Extras comuns no seu DB (se não existir no import, é só ignorar)
            'description'   => $name . ' - gerado pelo FakeProvider.',
            'cover'         => null,
            'status'        => 1,
            'technology'    => 'html5',
            'has_lobby'     => false,
            'is_mobile'     => true,
            'has_freespins' => ($type === 'slot'),
            'has_tables'    => in_array($type, ['table'], true),
            'only_demo'     => true,
            'rtp'           => 96.0,
            'distribution'  => 'fake',
            'is_featured'   => false,
            'show_home'     => false,

            // Se seu sistema usa URL de launch/iframe em algum lugar, pode ajustar depois
            'game_server_url' => $this->provider->baseUrl() ?: null,
        ];
    }
}