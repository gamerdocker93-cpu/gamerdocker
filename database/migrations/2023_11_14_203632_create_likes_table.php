<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Banco não virgem? Não tenta recriar
        if (Schema::hasTable('likes')) {
            return;
        }

        Schema::create('likes', function (Blueprint $table) {
            $table->id();

            // Compatível com users.id (bigint unsigned)
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('liked_user_id');

            // índices
            $table->index('user_id');
            $table->index('liked_user_id');

            $table->timestamps();

            // FKs
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('liked_user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            // evita duplicidade (mesmo par user/liked_user)
            $table->unique(['user_id', 'liked_user_id'], 'likes_user_liked_unique');

            // evita auto-like (MySQL 8+)
            $table->check('user_id <> liked_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};