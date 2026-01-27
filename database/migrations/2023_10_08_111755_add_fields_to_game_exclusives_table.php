<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Se a tabela não existe, NÃO tem o que alterar.
        if (!Schema::hasTable('game_exclusives')) {
            return;
        }

        Schema::table('game_exclusives', function (Blueprint $table) {
            // Adiciona somente se ainda não existir (evita crash em re-deploy)
            if (!Schema::hasColumn('game_exclusives', 'loseResults')) {
                $table->text('loseResults')->nullable();
            }
            if (!Schema::hasColumn('game_exclusives', 'demoWinResults')) {
                $table->text('demoWinResults')->nullable();
            }
            if (!Schema::hasColumn('game_exclusives', 'winResults')) {
                $table->text('winResults')->nullable();
            }
            if (!Schema::hasColumn('game_exclusives', 'iconsJson')) {
                $table->text('iconsJson')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('game_exclusives')) {
            return;
        }

        Schema::table('game_exclusives', function (Blueprint $table) {
            // Remove só se existir
            if (Schema::hasColumn('game_exclusives', 'loseResults')) {
                $table->dropColumn('loseResults');
            }
            if (Schema::hasColumn('game_exclusives', 'demoWinResults')) {
                $table->dropColumn('demoWinResults');
            }
            if (Schema::hasColumn('game_exclusives', 'winResults')) {
                $table->dropColumn('winResults');
            }
            if (Schema::hasColumn('game_exclusives', 'iconsJson')) {
                $table->dropColumn('iconsJson');
            }
        });
    }
};
