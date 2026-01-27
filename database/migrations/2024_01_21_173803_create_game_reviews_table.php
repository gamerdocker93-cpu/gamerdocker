<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('game_reviews')) {
            return;
        }

        Schema::create('game_reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('game_id');

            $table->index('user_id');
            $table->index('game_id');

            $table->string('description');
            $table->integer('rating')->default(0);

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('game_id')
                ->references('id')
                ->on('games')
                ->onDelete('cascade');

            // Chave primária composta (um review por usuário/jogo)
            $table->primary(['user_id', 'game_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_reviews');
    }
};