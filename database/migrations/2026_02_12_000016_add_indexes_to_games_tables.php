<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // games
        if (Schema::hasTable('games')) {
            Schema::table('games', function (Blueprint $table) {
                // Indexes (com nomes fixos pra conseguir dropar depois)
                if (Schema::hasColumn('games', 'game_code')) {
                    $table->index('game_code', 'idx_games_game_code');
                }

                if (Schema::hasColumn('games', 'provider_id')) {
                    $table->index('provider_id', 'idx_games_provider_id');
                }

                if (Schema::hasColumn('games', 'status')) {
                    $table->index('status', 'idx_games_status');
                }

                if (Schema::hasColumn('games', 'views')) {
                    $table->index('views', 'idx_games_views');
                }
            });
        }

        // category_game (pivot)
        if (Schema::hasTable('category_game')) {
            Schema::table('category_game', function (Blueprint $table) {
                if (Schema::hasColumn('category_game', 'category_id')) {
                    $table->index('category_id', 'idx_category_game_category_id');
                }

                if (Schema::hasColumn('category_game', 'game_id')) {
                    $table->index('game_id', 'idx_category_game_game_id');
                }

                // Combo index (ajuda MUITO no filtro por categoria + join)
                if (Schema::hasColumn('category_game', 'category_id') && Schema::hasColumn('category_game', 'game_id')) {
                    $table->index(['category_id', 'game_id'], 'idx_category_game_cat_game');
                }
            });
        }
    }

    public function down(): void
    {
        // games
        if (Schema::hasTable('games')) {
            Schema::table('games', function (Blueprint $table) {
                // dropIndex aceita o NOME do index
                $table->dropIndex('idx_games_game_code');
                $table->dropIndex('idx_games_provider_id');
                $table->dropIndex('idx_games_status');
                $table->dropIndex('idx_games_views');
            });
        }

        // category_game
        if (Schema::hasTable('category_game')) {
            Schema::table('category_game', function (Blueprint $table) {
                $table->dropIndex('idx_category_game_category_id');
                $table->dropIndex('idx_category_game_game_id');
                $table->dropIndex('idx_category_game_cat_game');
            });
        }
    }
};
