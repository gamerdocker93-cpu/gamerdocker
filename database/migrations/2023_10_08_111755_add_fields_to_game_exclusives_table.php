<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            // deixa NULL pra não quebrar inserts antigos
            if (!Schema::hasColumn('games', 'loseResults')) {
                $table->text('loseResults')->nullable();
            }
            if (!Schema::hasColumn('games', 'demoWinResults')) {
                $table->text('demoWinResults')->nullable();
            }
            if (!Schema::hasColumn('games', 'winResults')) {
                $table->text('winResults')->nullable();
            }
            if (!Schema::hasColumn('games', 'iconsJson')) {
                $table->text('iconsJson')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            if (Schema::hasColumn('games', 'loseResults')) $table->dropColumn('loseResults');
            if (Schema::hasColumn('games', 'demoWinResults')) $table->dropColumn('demoWinResults');
            if (Schema::hasColumn('games', 'winResults')) $table->dropColumn('winResults');
            if (Schema::hasColumn('games', 'iconsJson')) $table->dropColumn('iconsJson');
        });
    }
};
