<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GameProvider;

class GameProvidersSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['code' => 'ever', 'name' => 'Ever', 'enabled' => false],
            ['code' => 'venix', 'name' => 'Venix', 'enabled' => false],
            ['code' => 'worldslot', 'name' => 'WorldSlot', 'enabled' => false],
            ['code' => 'playgaming', 'name' => 'PlayGaming', 'enabled' => false],
            ['code' => 'games2api', 'name' => 'Games2Api', 'enabled' => false],
        ];

        foreach ($items as $it) {
            GameProvider::updateOrCreate(
                ['code' => $it['code']],
                $it
            );
        }
    }
}
