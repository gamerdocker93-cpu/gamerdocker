<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Banco não virgem? Não tenta recriar
        if (Schema::hasTable('ggr_games_fivers')) {
            return;
        }

        Schema::create('ggr_games_fivers', function (Blueprint $table) {
            $table->id();

            // Compatível com users.id (bigint unsigned)
            $table->unsignedBigInteger('user_id');
            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('provider', 191);
            $table->string('game', 191);

            $table->decimal('balance_bet', 20, 2)->default(0);
            $table->decimal('balance_win', 20, 2)->default(0);
            $table->string('currency', 50)->default('BRL');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ggr_games_fivers');
    }
};