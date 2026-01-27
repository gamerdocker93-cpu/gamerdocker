<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mission_users')) {
            return;
        }

        Schema::create('mission_users', function (Blueprint $table) {
            $table->id();

            // CORREÇÃO: compatível com users.id (bigint unsigned)
            $table->unsignedBigInteger('user_id');
            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // CORREÇÃO: compatível com missions.id (provavelmente bigint)
            $table->unsignedBigInteger('mission_id');
            $table->index('mission_id');
            $table->foreign('mission_id')->references('id')->on('missions')->onDelete('cascade');

            $table->bigInteger('rounds')->default(0);
            $table->decimal('rewards', 10, 2)->default(0);
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mission_users');
    }
};