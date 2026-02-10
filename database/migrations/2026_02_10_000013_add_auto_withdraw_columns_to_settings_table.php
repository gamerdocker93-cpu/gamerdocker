<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {

            // ===== AUTO WITHDRAW =====
            if (!Schema::hasColumn('settings', 'auto_withdraw_enabled')) {
                $table->tinyInteger('auto_withdraw_enabled')->default(0)->after('disable_rollover');
            }

            if (!Schema::hasColumn('settings', 'auto_withdraw_players')) {
                $table->tinyInteger('auto_withdraw_players')->default(0)->after('auto_withdraw_enabled');
            }

            if (!Schema::hasColumn('settings', 'auto_withdraw_affiliates')) {
                $table->tinyInteger('auto_withdraw_affiliates')->default(0)->after('auto_withdraw_players');
            }

            if (!Schema::hasColumn('settings', 'auto_withdraw_affiliate_enabled')) {
                $table->tinyInteger('auto_withdraw_affiliate_enabled')->default(0)->after('auto_withdraw_affiliates');
            }

            if (!Schema::hasColumn('settings', 'auto_withdraw_gateway')) {
                $table->string('auto_withdraw_gateway')->default('auto')->after('auto_withdraw_affiliate_enabled');
            }

            if (!Schema::hasColumn('settings', 'auto_withdraw_batch_size')) {
                $table->unsignedInteger('auto_withdraw_batch_size')->default(20)->after('auto_withdraw_gateway');
            }

            // ===== (Opcional, mas recomendado) compat com seu Settings.php atual =====
            // Seu Model/Filament usa digitopay_is_enable e sharkpay_is_enable.
            // Se não existirem, pode dar erro em outras telas.
            if (!Schema::hasColumn('settings', 'digitopay_is_enable')) {
                $table->boolean('digitopay_is_enable')->default(false)->after('bspay_is_enable');
            }

            if (!Schema::hasColumn('settings', 'sharkpay_is_enable')) {
                $table->boolean('sharkpay_is_enable')->default(false)->after('digitopay_is_enable');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {

            if (Schema::hasColumn('settings', 'sharkpay_is_enable')) {
                $table->dropColumn('sharkpay_is_enable');
            }

            if (Schema::hasColumn('settings', 'digitopay_is_enable')) {
                $table->dropColumn('digitopay_is_enable');
            }

            if (Schema::hasColumn('settings', 'auto_withdraw_batch_size')) {
                $table->dropColumn('auto_withdraw_batch_size');
            }

            if (Schema::hasColumn('settings', 'auto_withdraw_gateway')) {
                $table->dropColumn('auto_withdraw_gateway');
            }

            if (Schema::hasColumn('settings', 'auto_withdraw_affiliate_enabled')) {
                $table->dropColumn('auto_withdraw_affiliate_enabled');
            }

            if (Schema::hasColumn('settings', 'auto_withdraw_affiliates')) {
                $table->dropColumn('auto_withdraw_affiliates');
            }

            if (Schema::hasColumn('settings', 'auto_withdraw_players')) {
                $table->dropColumn('auto_withdraw_players');
            }

            if (Schema::hasColumn('settings', 'auto_withdraw_enabled')) {
                $table->dropColumn('auto_withdraw_enabled');
            }
        });
    }
};
