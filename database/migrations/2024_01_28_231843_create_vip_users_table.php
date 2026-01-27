<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vip_users')) {
            return;
        }

        Schema::create('vip_users', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('vip_id');
            $table->index('vip_id');
            $table->foreign('vip_id')->references('id')->on('vips')->onDelete('cascade');

            $table->bigInteger('level')->default(0);
            $table->bigInteger('points')->default(0);
            $table->tinyInteger('status')->default(0);

            $table->timestamps();

            // opcional: um registro vip por user (ajuste se seu negócio permitir múltiplos)
            $table->unique(['user_id', 'vip_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vip_users');
    }
};