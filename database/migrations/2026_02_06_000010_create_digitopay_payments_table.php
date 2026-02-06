<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // evita crash se por algum motivo rodar duas vezes
        if (Schema::hasTable('digitopay_payments')) {
            return;
        }

        Schema::create('digitopay_payments', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('payment_id', 191)->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('withdrawal_id')->nullable()->index();

            $table->string('pix_key', 191)->nullable();
            $table->string('pix_type', 50)->nullable();

            // dinheiro: decimal é o mais seguro
            $table->decimal('amount', 18, 2)->default(0);

            $table->text('observation')->nullable();

            // no model você usa '0' e '1'
            $table->string('status', 10)->default('0')->index();

            $table->timestamps();

            // FKs: só cria se as tabelas existirem (pra não quebrar seu deploy)
            // Se você tiver certeza que existem, eu deixo obrigatório depois.
            if (Schema::hasTable('users')) {
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }

            // Se existir tabela withdrawals/saques, você pode ajustar aqui depois.
            // Mantive sem FK para não estourar deploy caso a tabela tenha outro nome.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digitopay_payments');
    }
};
