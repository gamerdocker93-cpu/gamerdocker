<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Se a tabela não existe nesse ambiente, não quebra o deploy
        if (!Schema::hasTable('games')) {
            return;
        }

        // Calcula antes (mais seguro)
        $addLoseResults    = !Schema::hasColumn('games', 'loseResults');
        $addDemoWinResults = !Schema::hasColumn('games', 'demoWinResults');
        $addWinResults     = !Schema::hasColumn('games', 'winResults');
        $addIconsJson      = !Schema::hasColumn('games', 'iconsJson');

        if (!$addLoseResults && !$addDemoWinResults && !$addWinResults && !$addIconsJson) {
            return;
        }

        Schema::table('games', function (Blueprint $table) use (
            $addLoseResults,
            $addDemoWinResults,
            $addWinResults,
            $addIconsJson
        ) {
            // deixa NULL pra não quebrar inserts antigos
            if ($addLoseResults) {
                $table->text('loseResults')->nullable();
            }
            if ($addDemoWinResults) {
                $table->text('demoWinResults')->nullable();
            }
            if ($addWinResults) {
                $table->text('winResults')->nullable();
            }
            if ($addIconsJson) {
                $table->text('iconsJson')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('games')) {
            return;
        }

        $drop = [];
        if (Schema::hasColumn('games', 'loseResults')) $drop[] = 'loseResults';
        if (Schema::hasColumn('games', 'demoWinResults')) $drop[] = 'demoWinResults';
        if (Schema::hasColumn('games', 'winResults')) $drop[] = 'winResults';
        if (Schema::hasColumn('games', 'iconsJson')) $drop[] = 'iconsJson';

        if (empty($drop)) {
            return;
        }

        Schema::table('games', function (Blueprint $table) use ($drop) {
            $table->dropColumn($drop);
        });
    }
};