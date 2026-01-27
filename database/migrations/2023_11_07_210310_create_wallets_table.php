<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wallets')) {
            return;
        }

        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('currency', 20);
            $table->string('symbol', 5);
            $table->decimal('balance', 20, 2)->default(0);
            $table->decimal('balance_withdrawal', 20, 2)->default(0);
            $table->decimal('balance_bonus_rollover', 20, 2)->default(0);
            $table->decimal('balance_deposit_rollover', 20, 2)->default(0);
            $table->decimal('balance_bonus', 20, 2)->default(0);
            $table->decimal('balance_cryptocurrency', 20, 2)->default(0);
            $table->decimal('balance_demo', 20, 2)->default(0);
            $table->decimal('refer_rewards', 20, 2)->default(0);
            $table->tinyInteger('hide_balance')->default(0);
            $table->tinyInteger('active')->default(1);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('wallets')) {
            Schema::drop('wallets');
        }
    }
};