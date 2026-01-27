<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Banco não virgem? Não tenta recriar
        if (Schema::hasTable('affiliate_histories')) {
            return;
        }

        Schema::create('affiliate_histories', function (Blueprint $table) {
            $table->id();

            // CORREÇÃO: precisa bater com users.id (bigint unsigned)
            $table->unsignedBigInteger('user_id');
            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // CORREÇÃO: inviter também referencia users.id (bigint unsigned)
            $table->unsignedBigInteger('inviter');
            $table->index('inviter');
            $table->foreign('inviter')->references('id')->on('users')->onDelete('cascade');

            $table->decimal('commission', 20, 2)->default(0);
            $table->string('commission_type')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('deposited')->default(0);
            $table->decimal('deposited_amount', 10, 2)->default(0);
            $table->bigInteger('losses')->default(0);
            $table->decimal('losses_amount', 10, 2)->default(0);
            $table->decimal('commission_paid', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('affiliate_histories')) {
            Schema::drop('affiliate_histories');
        }
    }
};