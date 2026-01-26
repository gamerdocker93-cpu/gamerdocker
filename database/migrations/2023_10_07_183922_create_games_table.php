<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('games')) {
            return;
        }

        Schema::create('games', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('provider_id')->index();
            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');

            $table->string('game_server_url')->nullable();
            $table->string('game_id');
            $table->string('game_name');
            $table->string('game_code')->unique();
            $table->string('game_type')->nullable();
            $table->string('description')->nullable();
            $table->string('cover');
            $table->string('status');
            $table->string('technology')->nullable();

            $table->boolean('has_lobby')->default(false);
            $table->boolean('is_mobile')->default(false);
            $table->boolean('has_freespins')->default(false);
            $table->boolean('has_tables')->default(false);
            $table->boolean('only_demo')->default(false);

            $table->unsignedSmallInteger('rtp')->comment('Controle de RTP em porcentagem');
            $table->string('distribution')->comment('O nome do provedor');

            $table->unsignedBigInteger('views')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('show_home')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};