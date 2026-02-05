<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gateways', function (Blueprint $table) {

            // SharkPay
            if (!Schema::hasColumn('gateways', 'shark_public_key')) {
                $table->string('shark_public_key', 191)->nullable();
            }
            if (!Schema::hasColumn('gateways', 'shark_private_key')) {
                $table->string('shark_private_key', 191)->nullable();
            }

            // DigitoPay
            if (!Schema::hasColumn('gateways', 'digitopay_uri')) {
                $table->string('digitopay_uri', 191)->nullable();
            }
            if (!Schema::hasColumn('gateways', 'digitopay_cliente_id')) {
                $table->string('digitopay_cliente_id', 191)->nullable();
            }
            if (!Schema::hasColumn('gateways', 'digitopay_cliente_secret')) {
                $table->string('digitopay_cliente_secret', 191)->nullable();
            }

        });
    }

    public function down(): void
    {
        Schema::table('gateways', function (Blueprint $table) {

            if (Schema::hasColumn('gateways', 'shark_public_key')) {
                $table->dropColumn('shark_public_key');
            }
            if (Schema::hasColumn('gateways', 'shark_private_key')) {
                $table->dropColumn('shark_private_key');
            }

            if (Schema::hasColumn('gateways', 'digitopay_uri')) {
                $table->dropColumn('digitopay_uri');
            }
            if (Schema::hasColumn('gateways', 'digitopay_cliente_id')) {
                $table->dropColumn('digitopay_cliente_id');
            }
            if (Schema::hasColumn('gateways', 'digitopay_cliente_secret')) {
                $table->dropColumn('digitopay_cliente_secret');
            }

        });
    }
};
