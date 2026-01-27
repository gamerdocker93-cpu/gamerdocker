<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('game_likes')) {
            return;
        }

        Schema::create('game_likes', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('game_id');
            $table->timestamps();

            $table->index('user_id');
            $table->index('game_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');

            // Tabela pivô: chave primária composta (melhor que só unique)
            $table->primary(['user_id', 'game_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_likes');
    }
};