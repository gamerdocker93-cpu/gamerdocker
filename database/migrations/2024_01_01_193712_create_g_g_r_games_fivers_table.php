<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Se o banco não é virgem e a tabela já existe, não tenta recriar (evita 1050)
        if (Schema::hasTable('ggr_games_fivers')) {
            return;
        }

        // Se users não existe por algum motivo no ambiente, evita quebrar deploy
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::create('ggr_games_fivers', function (Blueprint $table) {
            $table->id();

            // Compatível com users.id (bigint unsigned)
            $table->unsignedBigInteger('user_id');
            $table->index('user_id');

            $table->string('provider', 191);
            $table->string('game', 191);

            $table->decimal('balance_bet', 20, 2)->default(0);
            $table->decimal('balance_win', 20, 2)->default(0);
            $table->string('currency', 50)->default('BRL');

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Nome certo (igual ao create)
        Schema::dropIfExists('ggr_games_fivers');
    }
};