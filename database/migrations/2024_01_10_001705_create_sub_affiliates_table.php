<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sub_affiliates')) {
            return;
        }

        Schema::create('sub_affiliates', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('affiliate_id');
            $table->index('affiliate_id');
            $table->foreign('affiliate_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('user_id');
            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->tinyInteger('status')->default(0);
            $table->timestamps();

            // opcional (recomendado): evita duplicar vínculo
            $table->unique(['affiliate_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_affiliates');
    }
};