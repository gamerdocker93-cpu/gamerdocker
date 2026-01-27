<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('game_favorites')) {
            return;
        }

        Schema::create('game_favorites', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('game_id');

            $table->index('user_id');
            $table->index('game_id');

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('game_id')
                ->references('id')
                ->on('games')
                ->onDelete('cascade');

            // Garante que um jogo só pode ser favorito por usuário
            $table->unique(['user_id', 'game_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_favorites');
    }
};