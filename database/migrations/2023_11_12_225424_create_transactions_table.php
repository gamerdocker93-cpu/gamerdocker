<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Se já existe (banco não virgem), não tenta recriar
        if (Schema::hasTable('transactions')) {
            return;
        }

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id', 100);

            // CORREÇÃO: precisa bater com users.id (bigint unsigned)
            $table->unsignedBigInteger('user_id');
            $table->index('user_id');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->string('payment_method')->nullable();
            $table->decimal('price', 20, 2)->default(0);
            $table->string('currency', 20)->default('usd');
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('transactions')) {
            Schema::drop('transactions');
        }
    }
};