<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DemoGamesSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('games')) {
            $this->command?->warn('Tabela "games" não existe. Abortando DemoGamesSeeder.');
            return;
        }

        $gamesCols = Schema::getColumnListing('games');

        $providerId = $this->ensureDemoProvider();

        $categories = $this->ensureCategories([
            ['name' => 'Slots',  'slug' => 'slots'],
            ['name' => 'Crash',  'slug' => 'crash'],
            ['name' => 'Roleta', 'slug' => 'roleta'],
            ['name' => 'Ao vivo','slug' => 'ao-vivo'],
        ]);

        $demoGames = $this->demoGamesList();

        foreach ($demoGames as $i => $g) {
            $code = $g['game_code'];

            // Monta dados respeitando colunas existentes
            $data = [];
            $data = $this->putIfCol($data, $gamesCols, 'provider_id', $providerId);
            $data = $this->putIfCol($data, $gamesCols, 'game_server_url', $g['game_server_url'] ?? 'demo://local');
            $data = $this->putIfCol($data, $gamesCols, 'game_id', $g['game_id'] ?? (string) (1000 + $i));
            $data = $this->putIfCol($data, $gamesCols, 'game_name', $g['game_name']);
            $data = $this->putIfCol($data, $gamesCols, 'game_code', $code);
            $data = $this->putIfCol($data, $gamesCols, 'game_type', $g['game_type'] ?? 'slot');
            $data = $this->putIfCol($data, $gamesCols, 'description', $g['description'] ?? 'Jogo DEMO para teste do sistema.');
            $data = $this->putIfCol($data, $gamesCols, 'cover', $g['cover'] ?? 'https://via.placeholder.com/512x512.png?text=DEMO');
            $data = $this->putIfCol($data, $gamesCols, 'status', $g['status'] ?? 1);
            $data = $this->putIfCol($data, $gamesCols, 'technology', $g['technology'] ?? 'html5');

            // flags comuns
            $data = $this->putIfCol($data, $gamesCols, 'has_lobby', $g['has_lobby'] ?? 0);
            $data = $this->putIfCol($data, $gamesCols, 'is_mobile', $g['is_mobile'] ?? 1);
            $data = $this->putIfCol($data, $gamesCols, 'has_freespins', $g['has_freespins'] ?? 0);
            $data = $this->putIfCol($data, $gamesCols, 'has_tables', $g['has_tables'] ?? 0);
            $data = $this->putIfCol($data, $gamesCols, 'only_demo', $g['only_demo'] ?? 1);

            // números
            $data = $this->putIfCol($data, $gamesCols, 'rtp', $g['rtp'] ?? (string) (92 + ($i % 7))); // 92..98
            $data = $this->putIfCol($data, $gamesCols, 'distribution', $g['distribution'] ?? 'demo');
            $data = $this->putIfCol($data, $gamesCols, 'views', $g['views'] ?? 0);
            $data = $this->putIfCol($data, $gamesCols, 'is_featured', $g['is_featured'] ?? (($i % 8) === 0 ? 1 : 0));
            $data = $this->putIfCol($data, $gamesCols, 'show_home', $g['show_home'] ?? 1);

            // timestamps
            $now = now();
            $data = $this->putIfCol($data, $gamesCols, 'created_at', $now);
            $data = $this->putIfCol($data, $gamesCols, 'updated_at', $now);

            // Idempotente: atualiza se já existir pelo game_code (se a coluna existir)
            if (in_array('game_code', $gamesCols, true)) {
                DB::table('games')->updateOrInsert(['game_code' => $code], $data);
                $gameId = DB::table('games')->where('game_code', $code)->value('id');
            } else {
                // fallback (raro): insere sempre
                $gameId = DB::table('games')->insertGetId($data);
            }

            // Vincular categoria se existir pivot
            $this->attachCategory($gameId, $g['category'] ?? null, $categories);
        }

        $this->command?->info('DEMO: jogos inseridos/atualizados com sucesso.');
    }

    private function putIfCol(array $data, array $cols, string $col, $value): array
    {
        if (in_array($col, $cols, true)) {
            $data[$col] = $value;
        }
        return $data;
    }

    private function ensureDemoProvider(): ?int
    {
        if (!Schema::hasTable('providers')) {
            return null;
        }

        $cols = Schema::getColumnListing('providers');

        // tenta encontrar por slug/code/name (o que existir)
        $q = DB::table('providers');
        if (in_array('slug', $cols, true)) $q->orWhere('slug', 'demo');
        if (in_array('code', $cols, true)) $q->orWhere('code', 'demo');
        if (in_array('name', $cols, true)) $q->orWhere('name', 'DEMO');

        $existing = $q->first();
        if ($existing && isset($existing->id)) {
            return (int) $existing->id;
        }

        $now = now();
        $row = [];
        if (in_array('name', $cols, true)) $row['name'] = 'DEMO';
        if (in_array('slug', $cols, true)) $row['slug'] = 'demo';
        if (in_array('code', $cols, true)) $row['code'] = 'demo';
        if (in_array('status', $cols, true)) $row['status'] = 1;
        if (in_array('created_at', $cols, true)) $row['created_at'] = $now;
        if (in_array('updated_at', $cols, true)) $row['updated_at'] = $now;

        // garante que não vai dar insert vazio
        if (empty($row)) {
            // se a tabela providers tiver colunas inesperadas, não arriscamos
            return null;
        }

        return (int) DB::table('providers')->insertGetId($row);
    }

    private function ensureCategories(array $list): array
    {
        if (!Schema::hasTable('categories')) {
            return [];
        }

        $cols = Schema::getColumnListing('categories');
        $out = [];

        foreach ($list as $c) {
            $now = now();
            $row = [];

            if (in_array('name', $cols, true)) $row['name'] = $c['name'];
            if (in_array('slug', $cols, true)) $row['slug'] = $c['slug'];
            if (in_array('status', $cols, true)) $row['status'] = 1;
            if (in_array('created_at', $cols, true)) $row['created_at'] = $now;
            if (in_array('updated_at', $cols, true)) $row['updated_at'] = $now;

            // se não tiver nem name nem slug, não dá pra criar
            if (!in_array('name', $cols, true) && !in_array('slug', $cols, true)) {
                continue;
            }

            // chave de busca: slug se existir, senão name
            $where = [];
            if (in_array('slug', $cols, true)) {
                $where['slug'] = $c['slug'];
            } else {
                $where['name'] = $c['name'];
            }

            DB::table('categories')->updateOrInsert($where, $row);

            // pega id
            $idQ = DB::table('categories');
            foreach ($where as $k => $v) $idQ->where($k, $v);
            $id = $idQ->value('id');

            if ($id) $out[$c['slug']] = (int) $id;
        }

        return $out;
    }

    private function attachCategory(?int $gameId, ?string $catSlug, array $categories): void
    {
        if (!$gameId || !$catSlug) return;
        if (!Schema::hasTable('category_game')) return;
        if (!isset($categories[$catSlug])) return;

        $cols = Schema::getColumnListing('category_game');
        if (!in_array('category_id', $cols, true) || !in_array('game_id', $cols, true)) return;

        DB::table('category_game')->updateOrInsert(
            ['category_id' => $categories[$catSlug], 'game_id' => $gameId],
            []
        );
    }

    private function demoGamesList(): array
    {
        // Você pode aumentar/alterar aqui à vontade
        return [
            ['game_name' => 'DEMO - Fortune Tiger', 'game_code' => 'demo_fortune_tiger', 'game_type' => 'slot', 'category' => 'slots'],
            ['game_name' => 'DEMO - Gates of Olympus', 'game_code' => 'demo_gates_olympus', 'game_type' => 'slot', 'category' => 'slots'],
            ['game_name' => 'DEMO - Sweet Bonanza', 'game_code' => 'demo_sweet_bonanza', 'game_type' => 'slot', 'category' => 'slots'],
            ['game_name' => 'DEMO - Big Bass', 'game_code' => 'demo_big_bass', 'game_type' => 'slot', 'category' => 'slots'],
            ['game_name' => 'DEMO - Aviator Clone', 'game_code' => 'demo_aviator', 'game_type' => 'crash', 'category' => 'crash', 'has_lobby' => 0, 'has_tables' => 0],
            ['game_name' => 'DEMO - Crash X', 'game_code' => 'demo_crash_x', 'game_type' => 'crash', 'category' => 'crash'],
            ['game_name' => 'DEMO - Roleta Europeia', 'game_code' => 'demo_roleta_eu', 'game_type' => 'roulette', 'category' => 'roleta', 'has_tables' => 1],
            ['game_name' => 'DEMO - Roleta AO VIVO', 'game_code' => 'demo_roleta_live', 'game_type' => 'roulette', 'category' => 'ao-vivo', 'has_tables' => 1],
            ['game_name' => 'DEMO - Blackjack', 'game_code' => 'demo_blackjack', 'game_type' => 'cards', 'category' => 'ao-vivo', 'has_tables' => 1],
            ['game_name' => 'DEMO - Poker', 'game_code' => 'demo_poker', 'game_type' => 'cards', 'category' => 'ao-vivo', 'has_tables' => 1],
            // +20 slots fáceis
            ...collect(range(1, 20))->map(fn ($n) => [
                'game_name' => "DEMO - Slot {$n}",
                'game_code' => "demo_slot_{$n}",
                'game_type' => 'slot',
                'category' => 'slots',
                'rtp' => (string) (92 + ($n % 7)),
            ])->all(),
        ];
    }
}
